<?php
$pageTitle = 'Lumiere Books | Premium Online Book Store';
require_once 'includes/header.php';

// Home page සඳහා featured books සහ new release books ලබා ගැනීම.
$featuredStmt = $pdo->query("SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.status='active' AND b.is_featured=1 ORDER BY b.created_at DESC LIMIT 8");
$featuredBooks = $featuredStmt->fetchAll();
$newStmt = $pdo->query("SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.status='active' AND b.is_new=1 ORDER BY b.created_at DESC LIMIT 6");
$newBooks = $newStmt->fetchAll();
?>
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Premium Online Book Store</span>
            <h1>Discover stories, knowledge and imagination in one clear modern space.</h1>
            <p>Lumiere Books helps customers search, explore, wishlist and purchase books online with a smooth white-theme shopping experience.</p>
            <div class="hero-actions">
                <a href="books.php" class="btn">Explore Books</a>
                <a href="books.php?sort=new" class="btn btn-light">New Releases</a>
            </div>
            <div class="hero-stats">
                <div><strong>500+</strong><span>Books Ready</span></div>
                <div><strong>24h</strong><span>Fast Processing</span></div>
                <div><strong>Secure</strong><span>Login & Checkout</span></div>
            </div>
        </div>
        <div class="hero-art">
            <img src="https://img.freepik.com/premium-photo/there-is-wallpaper-with-books-plants-it-generative-ai_955884-88581.jpg" alt="Modern bookstore shelves">
        </div>
    </div>
</section>

<section class="container section-pad">
    <div class="section-heading">
        <span class="eyebrow">Browse Collections</span>
        <h2>Popular Categories</h2>
        <p>Choose books by category just like a real online bookstore.</p>
    </div>
    <div class="category-grid visual-categories">
        <?php foreach ($categories as $cat): ?>
            <a class="category-card visual-category-card" href="books.php?category=<?= (int)$cat['id'] ?>">
                <img src="<?= e(categoryImagePath($cat['image'] ?? '')) ?>" alt="<?= e($cat['name']) ?> category">
                <strong><?= e($cat['name']) ?></strong>
                <small><?= e($cat['description']) ?></small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="container section-pad">
    <div class="section-heading row-heading">
        <div>
            <span class="eyebrow">Top Shelf Reads</span>
            <h2>Featured Books</h2>
        </div>
        <a href="books.php" class="link-arrow">View all →</a>
    </div>
    <div class="book-grid">
        <?php foreach ($featuredBooks as $book): include 'includes/book-card.php'; endforeach; ?>
    </div>
</section>

<section class="promo-banner container">
    <div>
        <span class="eyebrow">Special Offer</span>
        <h2>Save more on selected educational and fiction books.</h2>
        <p>Discounted prices, stock badges, clear book information and easy cart management included.</p>
        <a class="btn" href="books.php?sort=discount">Shop Offers</a>
    </div>
    <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80" alt="Real books on a reading table">
</section>

<section class="container section-pad">
    <div class="section-heading row-heading">
        <div>
            <span class="eyebrow">Fresh Arrivals</span>
            <h2>New Releases</h2>
        </div>
        <a href="books.php?sort=new" class="link-arrow">Explore new →</a>
    </div>
    <div class="book-grid compact">
        <?php foreach ($newBooks as $book): include 'includes/book-card.php'; endforeach; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
