<?php
$pageTitle = 'Shopping Cart | Lumiere Books';
require_once 'includes/header.php';
if (!isLoggedIn()) {
    setFlash('error', 'Please login to view your cart.');
    redirect('login.php');
}
$items = getCartItems($pdo);
$total = cartTotal($items);
?>
<section class="page-hero slim"><div class="container"><span class="eyebrow">Shopping Cart</span><h1>Your selected books</h1></div></section>
<section class="container section-pad cart-layout">
    <div class="cart-table-wrap">
        <?php if ($items): ?>
            <table class="cart-table">
                <thead><tr><th>Book</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): $price = $item['discount_price'] ?: $item['price']; ?>
                    <tr>
                        <td class="cart-book"><img src="<?= e(imagePath($item['cover_image'])) ?>" alt=""><div><strong><?= e($item['title']) ?></strong><small><?= e($item['author']) ?></small></div></td>
                        <td><?= money($price) ?></td>
                        <td>
                            <form action="actions/update_cart.php" method="post" class="inline-qty">
                                <input type="hidden" name="cart_id" value="<?= (int)$item['id'] ?>">
                                <input type="number" min="1" max="<?= max(1, (int)$item['stock']) ?>" name="quantity" value="<?= (int)$item['quantity'] ?>">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td><?= money($price * $item['quantity']) ?></td>
                        <td><a class="remove-link" href="actions/remove_cart.php?id=<?= (int)$item['id'] ?>">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state"><img src="assets/images/empty-cart.png" alt="Empty cart"><h2>Your cart is empty</h2><p>Add books to continue checkout.</p><a class="btn" href="books.php">Shop Now</a></div>
        <?php endif; ?>
    </div>
    <aside class="order-summary">
        <h3>Order Summary</h3>
        <div class="summary-row"><span>Subtotal</span><strong><?= money($total) ?></strong></div>
        <div class="summary-row"><span>Delivery</span><strong><?= $total > 0 ? money(350) : money(0) ?></strong></div>
        <div class="summary-row total"><span>Total</span><strong><?= money($total > 0 ? $total + 350 : 0) ?></strong></div>
        <a class="btn full <?= !$items ? 'disabled' : '' ?>" href="<?= $items ? 'checkout.php' : '#' ?>">Proceed to Checkout</a>
    </aside>
</section>
<?php require_once 'includes/footer.php'; ?>
