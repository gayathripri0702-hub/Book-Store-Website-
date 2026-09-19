<?php
$pageTitle = 'My Orders | Lumiere Books';
require_once 'includes/header.php';
if (!isLoggedIn()) {
    setFlash('error', 'Please login to view orders.');
    redirect('login.php');
}
// current user ගේ order history payment details සමඟ ලබා ගැනීම.
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user']['id']]);
$orders = $stmt->fetchAll();
?>
<section class="page-hero slim"><div class="container"><span class="eyebrow">Order History</span><h1>Track your purchases</h1></div></section>
<section class="container section-pad">
<?php if ($orders): ?>
    <div class="orders-list">
        <?php foreach ($orders as $order): ?>
            <?php
            $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$order['id']]);
            $orderItems = $itemsStmt->fetchAll();
            ?>
            <article class="order-card">
                <div class="order-head">
                    <div><strong>#<?= e($order['order_number']) ?></strong><small><?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></small></div>
                    <div class="status-stack"><span class="status-pill <?= strtolower($order['status']) ?>"><?= e($order['status']) ?></span><span class="status-pill payment-status"><?= e($order['payment_status']) ?></span></div>
                </div>
                <div class="payment-mini">
                    <span><strong>Payment:</strong> <?= e($order['payment_method']) ?></span>
                    <span><strong>Transaction:</strong> <?= e($order['transaction_id']) ?></span>
                </div>
                <div class="order-lines">
                    <?php foreach ($orderItems as $it): ?>
                        <div><span><?= e($it['title']) ?> × <?= (int)$it['quantity'] ?></span><strong><?= money($it['subtotal']) ?></strong></div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-row"><span>Subtotal</span><strong><?= money($order['subtotal_amount']) ?></strong></div>
                <div class="summary-row"><span>Delivery</span><strong><?= money($order['delivery_fee']) ?></strong></div>
                <div class="summary-row total"><span>Total</span><strong><?= money($order['total_amount']) ?></strong></div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state"><img src="assets/images/empty-cart.png" alt="No orders"><h2>No orders yet</h2><p>Your purchased books will appear here.</p><a class="btn" href="books.php">Start Shopping</a></div>
<?php endif; ?>
</section>
<?php require_once 'includes/footer.php'; ?>
