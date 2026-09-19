<?php
$pageTitle = 'Checkout | Lumiere Books';
require_once 'includes/header.php';
if (!isLoggedIn()) {
    setFlash('error', 'Please login to checkout.');
    redirect('login.php');
}
$items = getCartItems($pdo);
if (!$items) {
    setFlash('error', 'Your cart is empty.');
    redirect('books.php');
}
$subtotal = cartTotal($items);
$delivery = 350;
$total = $subtotal + $delivery;

// checkout submit වූ විට transaction එකක් තුළ order, order items සහ payment record create කිරීම.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment = trim($_POST['payment_method'] ?? 'Cash on Delivery');
    $allowedPayments = ['Cash on Delivery', 'Card Payment', 'Bank Transfer'];

    try {
        if (!in_array($payment, $allowedPayments, true)) {
            throw new Exception('Please choose a valid payment method.');
        }
        if ($name === '' || $email === '' || $phone === '' || $address === '') {
            throw new Exception('Please complete all delivery details.');
        }

        $transactionId = makeTransactionId();
        $paymentStatus = 'Pending';
        $orderStatus = 'Pending';
        $cardBrand = null;
        $cardLast4 = null;
        $bankReference = null;
        $slipImage = null;

        if ($payment === 'Card Payment') {
            // real gateway එකක් වගේ card validation කරයි. full card/CVV database එකට save නොකරයි.
            $cardNumber = $_POST['card_number'] ?? '';
            $cardHolder = trim($_POST['card_holder'] ?? '');
            $expiry = trim($_POST['card_expiry'] ?? '');
            $cvv = trim($_POST['card_cvv'] ?? '');
            $digits = preg_replace('/\D+/', '', $cardNumber);
            if ($cardHolder === '' || $expiry === '' || $cvv === '') {
                throw new Exception('Please complete card payment details.');
            }
            if (!validateCardNumber($digits)) {
                throw new Exception('Invalid card number. For demo use 4111 1111 1111 1111.');
            }
            if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $m)) {
                throw new Exception('Card expiry must be MM/YY format.');
            }
            $expiryMonth = (int)$m[1];
            $expiryYear = 2000 + (int)$m[2];
            $expiryDate = DateTime::createFromFormat('Y-n-j', $expiryYear . '-' . $expiryMonth . '-1')->modify('last day of this month');
            if ($expiryDate < new DateTime('today')) {
                throw new Exception('Card has expired.');
            }
            if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
                throw new Exception('CVV must have 3 or 4 digits.');
            }
            $paymentStatus = 'Paid';
            $orderStatus = 'Processing';
            $cardBrand = detectCardBrand($digits);
            $cardLast4 = substr($digits, -4);
        }

        if ($payment === 'Bank Transfer') {
            // bank transfer වලදී reference number සහ slip image optionally save කරයි.
            $bankReference = trim($_POST['bank_reference'] ?? '');
            if ($bankReference === '') {
                throw new Exception('Please enter bank transfer reference number.');
            }
            $uploadedSlip = uploadImage('bank_slip', __DIR__ . '/uploads/payments', 'slip');
            $slipImage = $uploadedSlip ? 'uploads/payments/' . $uploadedSlip : null;
            $paymentStatus = 'Verification Pending';
            $orderStatus = 'Pending';
        }

        $orderNumber = makeOrderNumber();
        $pdo->beginTransaction();

        $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, customer_name, email, phone, address, payment_method, subtotal_amount, delivery_fee, total_amount, payment_status, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $orderStmt->execute([$_SESSION['user']['id'], $orderNumber, $name, $email, $phone, $address, $payment, $subtotal, $delivery, $total, $paymentStatus, $transactionId, $orderStatus]);
        $orderId = $pdo->lastInsertId();

        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, book_id, title, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $stockStmt = $pdo->prepare("UPDATE books SET stock = stock - ? WHERE id = ? AND stock >= ?");
        foreach ($items as $item) {
            $price = $item['discount_price'] ?: $item['price'];
            $lineTotal = $price * $item['quantity'];
            $itemStmt->execute([$orderId, $item['book_id'], $item['title'], $price, $item['quantity'], $lineTotal]);
            $stockStmt->execute([$item['quantity'], $item['book_id'], $item['quantity']]);
            if ($stockStmt->rowCount() !== 1) {
                // stock ප්‍රමාණය ප්‍රමාණවත් නැතිනම් order එක cancel කරයි.
                throw new Exception('Insufficient stock for ' . $item['title']);
            }
        }

        $paymentStmt = $pdo->prepare("INSERT INTO payments (order_id, transaction_id, payment_method, amount, status, card_brand, card_last4, bank_reference, slip_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $paymentStmt->execute([$orderId, $transactionId, $payment, $total, $paymentStatus, $cardBrand, $cardLast4, $bankReference, $slipImage]);

        $clear = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $clear->execute([$_SESSION['user']['id']]);
        $pdo->commit();
        redirect('payment_success.php?order=' . urlencode($orderNumber));
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setFlash('error', $e->getMessage());
        redirect('checkout.php');
    }
}
$userStmt = $pdo->prepare("SELECT name, email, phone, address FROM users WHERE id = ? LIMIT 1");
$userStmt->execute([$_SESSION['user']['id']]);
$user = $userStmt->fetch() ?: currentUser();
?>
<section class="page-hero slim"><div class="container"><span class="eyebrow">Secure Checkout</span><h1>Complete your order</h1><p>Real checkout flow style: delivery details, card validation, bank transfer proof and transaction references.</p></div></section>
<section class="container section-pad checkout-grid">
    <form method="post" enctype="multipart/form-data" class="checkout-form" id="checkoutForm">
        <h3>Delivery Details</h3>
        <label>Customer Name</label>
        <input type="text" name="customer_name" value="<?= e($user['name']) ?>" required>
        <label>Email</label>
        <input type="email" name="email" value="<?= e($user['email']) ?>" required>
        <label>Phone</label>
        <input type="text" name="phone" required placeholder="07xxxxxxxx" value="<?= e($user['phone'] ?? '') ?>">
        <label>Delivery Address</label>
        <textarea name="address" rows="4" required placeholder="Enter full delivery address"><?= e($user['address'] ?? '') ?></textarea>

        <h3 class="payment-heading">Payment Method</h3>
        <div class="payment-methods">
            <label class="payment-card"><input type="radio" name="payment_method" value="Cash on Delivery" checked> <span>Cash on Delivery<small>Pay when books are delivered.</small></span></label>
            <label class="payment-card"><input type="radio" name="payment_method" value="Card Payment"> <span>Card Payment<small>Demo gateway validation with transaction ID.</small></span></label>
            <label class="payment-card"><input type="radio" name="payment_method" value="Bank Transfer"> <span>Bank Transfer<small>Upload transfer proof for verification.</small></span></label>
        </div>

        <div class="payment-box" data-payment-box="Card Payment">
            <div class="demo-box">Demo card: <strong>4111 1111 1111 1111</strong>, future expiry, any 3-digit CVV. Full card/CVV will not be stored.</div>
            <label>Card Holder Name</label><input type="text" name="card_holder" placeholder="Name on card">
            <label>Card Number</label><input type="text" name="card_number" inputmode="numeric" placeholder="4111 1111 1111 1111">
            <div class="form-grid mini-grid"><div><label>Expiry</label><input type="text" name="card_expiry" placeholder="MM/YY"></div><div><label>CVV</label><input type="password" name="card_cvv" maxlength="4" placeholder="123"></div></div>
        </div>

        <div class="payment-box" data-payment-box="Bank Transfer">
            <div class="bank-details">
                <strong>Bank Details</strong>
                <span>Account Name: Lumiere Books</span>
                <span>Bank: Bank of Ceylon</span>
                <span>Account No: 1234567890</span>
                <span>Branch: Colombo</span>
            </div>
            <label>Bank Transfer Reference</label><input type="text" name="bank_reference" placeholder="Receipt / transaction reference">
            <label>Upload Payment Slip</label><input type="file" name="bank_slip" accept="image/*">
        </div>

        <button class="btn full" type="submit">Place Secure Order</button>
    </form>
    <aside class="order-summary sticky-summary">
        <h3>Order Summary</h3>
        <?php foreach ($items as $item): $price = $item['discount_price'] ?: $item['price']; ?>
            <div class="summary-row"><span><?= e($item['title']) ?> × <?= (int)$item['quantity'] ?></span><strong><?= money($price * $item['quantity']) ?></strong></div>
        <?php endforeach; ?>
        <hr>
        <div class="summary-row"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
        <div class="summary-row"><span>Delivery</span><strong><?= money($delivery) ?></strong></div>
        <div class="summary-row total"><span>Total</span><strong><?= money($total) ?></strong></div>
        <div class="secure-note">🔒 Secure payment-style checkout with protected order processing.</div>
    </aside>
</section>
<?php require_once 'includes/footer.php'; ?>
