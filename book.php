<?php
require_once 'includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$book = getBookById($pdo, $id);
if (!$book) {
    setFlash('error', 'Book not found.');
    redirect('books.php');
}
$pageTitle = $book['title'] . ' | Lumiere Books';
require_once 'includes/header.php';
$price = $book['discount_price'] ?: $book['price'];
$hasDiscount = !empty($book['discount_price']) && $book['discount_price'] < $book['price'];
?>
<section class="container product-page section-pad">
    <div class="product-image-wrap">
        <img src="<?= e(imagePath($book['cover_image'])) ?>" alt="<?= e($book['title']) ?> cover">
    </div>
    <div class="product-details">
        <span class="eyebrow"><?= e($book['category_name']) ?></span>
        <h1><?= e($book['title']) ?></h1>
        <p class="author">By <?= e($book['author']) ?></p>
        <div class="product-price">
            <strong><?= money($price) ?></strong>
            <?php if ($hasDiscount): ?><del><?= money($book['price']) ?></del><?php endif; ?>
        </div>
        <div class="stock-badge <?= $book['stock'] > 0 ? 'in' : 'out' ?>">
            <?= $book['stock'] > 0 ? 'In Stock: ' . (int)$book['stock'] : 'Out of stock' ?>
        </div>
        <p class="description"><?= nl2br(e($book['description'])) ?></p>
        <ul class="book-meta">
            <li><strong>ISBN:</strong> <?= e($book['isbn']) ?></li>
            <li><strong>Language:</strong> <?= e($book['language']) ?></li>
            <li><strong>Category:</strong> <?= e($book['category_name']) ?></li>
        </ul>
        <div class="product-actions">
            <form action="actions/add_to_cart.php" method="post" class="qty-form">
                <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
                <input type="number" name="quantity" min="1" max="<?= max(1, (int)$book['stock']) ?>" value="1">
                <button class="btn" type="submit" <?= $book['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
            </form>
            <form action="actions/toggle_wishlist.php" method="post">
                <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
                <button class="btn btn-light" type="submit">Add to Wishlist</button>
            </form>
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
