<?php
$pageTitle = 'Shop Books | Lumiere Books';
require_once 'includes/header.php';

// Search, category filter සහ sorting values ලබා ගැනීම.
$q = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'latest';

$where = ["b.status = 'active'"];
$params = [];
if ($q !== '') {
    $where[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.description LIKE ?)";
    $search = "%$q%";
    array_push($params, $search, $search, $search, $search);
}
if ($categoryId > 0) {
    $where[] = "b.category_id = ?";
    $params[] = $categoryId;
}
$orderBy = "b.created_at DESC";
if ($sort === 'price_low') $orderBy = "COALESCE(b.discount_price, b.price) ASC";
if ($sort === 'price_high') $orderBy = "COALESCE(b.discount_price, b.price) DESC";
if ($sort === 'new') $orderBy = "b.is_new DESC, b.created_at DESC";
if ($sort === 'discount') $orderBy = "b.discount_price IS NULL, b.discount_price ASC";

$sql = "SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();
?>
<section class="page-hero slim">
    <div class="container">
        <span class="eyebrow">Online Shop</span>
        <h1>Find your next favourite book</h1>
        <p>Search by title, author, ISBN or select a category to filter the catalogue.</p>
    </div>
</section>

<section class="container shop-layout section-pad">
    <aside class="filter-panel">
        <form method="get" action="books.php">
            <h3>Filters</h3>
            <label>Search</label>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Book title, author, ISBN">
            <label>Category</label>
            <select name="category">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Sort by</label>
            <select name="sort">
                <option value="latest" <?= $sort==='latest'?'selected':'' ?>>Latest</option>
                <option value="new" <?= $sort==='new'?'selected':'' ?>>New Releases</option>
                <option value="discount" <?= $sort==='discount'?'selected':'' ?>>Special Offers</option>
                <option value="price_low" <?= $sort==='price_low'?'selected':'' ?>>Price: Low to High</option>
                <option value="price_high" <?= $sort==='price_high'?'selected':'' ?>>Price: High to Low</option>
            </select>
            <button class="btn full" type="submit">Apply Filters</button>
            <a class="clear-link" href="books.php">Clear all</a>
        </form>
    </aside>
    <div class="shop-results">
        <div class="result-summary">
            <strong><?= count($books) ?></strong> books found
        </div>
        <?php if ($books): ?>
            <div class="book-grid">
                <?php foreach ($books as $book): include 'includes/book-card.php'; endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <img src="assets/images/empty-shelf.png" alt="No books found">
                <h2>No books found</h2>
                <p>Try another search keyword or category.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
