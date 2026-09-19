<?php
$pageTitle = 'Login | Lumiere Books';
require_once 'includes/header.php';

// login form submit වූ විට email/password verify කිරීම.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'phone' => $user['phone'],
            'address' => $user['address']
        ];
        setFlash('success', 'Welcome back, ' . $user['name'] . '!');
        redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
    } else {
        setFlash('error', 'Invalid email or password.');
        redirect('login.php');
    }
}
?>
<section class="auth-page section-pad">
    <div class="auth-card">
        <span class="eyebrow">Welcome back</span>
        <h1>Login to your account</h1>
        <p>Access your cart, wishlist and order history securely.</p>
        <form method="post" class="form-stack">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="you@example.com">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter password">
            <button class="btn full" type="submit">Login</button>
        </form>
        <p class="auth-note">No account yet? <a href="register.php">Create one</a></p>
        <div class="demo-box">
            <strong>Demo Admin:</strong> admin@lumiere.lk / admin123<br>
            <strong>Demo User:</strong> customer@lumiere.lk / customer123
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
