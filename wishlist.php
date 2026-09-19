<?php
$pageTitle = 'Wishlist | Lumiere Books';
require_once 'includes/header.php';
if (!isLoggedIn()) {
    setFlash('error', 'Please login to view wishlist.');
    redirect('login.php');
}
// user wishlist books ලබා ගැනීම.
$stmt = $pdo->prepare("SELECT b.*, c.name AS category_name FROM wishlist w INNER JOIN books b ON w.book_id=b.id LEFT JOIN categories c ON b.category_id=c.id WHERE w.user_id=? AND b.status='active' ORDER BY w.created_at DESC");
$stmt->execute([$_SESSION['user']['id']]);
$books = $stmt->fetchAll();
?>
<section class="page-hero slim"><div class="container"><span class="eyebrow">Wishlist</span><h1>Books you love</h1></div></section>
<section class="container section-pad">
    <?php if ($books): ?>
        <div class="book-grid"><?php foreach ($books as $book): include 'includes/book-card.php'; endforeach; ?></div>
    <?php else: ?>
        <div class="empty-state"><img src="assets/images/empty-shelf.png" alt="Empty wishlist"><h2>Your wishlist is empty</h2><p>Save your favourite books here.</p><a class="btn" href="books.php">Explore Books</a></div>
    <?php endif; ?>
</section>
<?php require_once 'includes/footer.php'; ?>
