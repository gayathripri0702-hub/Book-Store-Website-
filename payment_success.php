<?php
$pageTitle = 'Payment Success | Lumiere Books';
require_once 'includes/header.php';
if (!isLoggedIn()) {
    redirect('login.php');
}
$orderNumber = trim($_GET['order'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$orderNumber, $_SESSION['user']['id']]);
$order = $stmt->fetch();
if (!$order) {
    setFlash('error', 'Order not found.');
    redirect('orders.php');
}
?>
<section class="container section-pad success-wrap">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <span class="eyebrow">Order Completed</span>
        <h1>Thank you for your purchase!</h1>
        <p>Your order has been created successfully. Keep the order number and transaction reference for future tracking.</p>
        <div class="success-details">
            <div><span>Order Number</span><strong>#<?= e($order['order_number']) ?></strong></div>
            <div><span>Payment Method</span><strong><?= e($order['payment_method']) ?></strong></div>
            <div><span>Payment Status</span><strong><?= e($order['payment_status']) ?></strong></div>
            <div><span>Transaction ID</span><strong><?= e($order['transaction_id']) ?></strong></div>
            <div><span>Total</span><strong><?= money($order['total_amount']) ?></strong></div>
        </div>
        <div class="hero-actions center-actions">
            <a class="btn" href="orders.php">View My Orders</a>
            <a class="btn btn-light" href="books.php">Continue Shopping</a>
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
