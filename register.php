<?php
$pageTitle = 'Register | Lumiere Books';
require_once 'includes/header.php';

// customer registration data validate කර database එකට save කිරීම.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        setFlash('error', 'Passwords do not match.');
        redirect('register.php');
    }
    if (strlen($password) < 6) {
        setFlash('error', 'Password must be at least 6 characters.');
        redirect('register.php');
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        setFlash('error', 'This email is already registered.');
        redirect('register.php');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
    $stmt->execute([$name, $email, $hash, $phone, $address]);
    setFlash('success', 'Account created successfully. Please login.');
    redirect('login.php');
}
?>
<section class="auth-page section-pad">
    <div class="auth-card wide">
        <span class="eyebrow">Join Lumiere</span>
        <h1>Create your account</h1>
        <form method="post" class="form-grid">
            <div>
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="Your name">
            </div>
            <div>
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="you@example.com">
            </div>
            <div>
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="07xxxxxxxx">
            </div>
            <div>
                <label>Delivery Address</label>
                <input type="text" name="address" placeholder="Your address">
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" required placeholder="Minimum 6 characters">
            </div>
            <div>
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required placeholder="Repeat password">
            </div>
            <button class="btn full-span" type="submit">Create Account</button>
        </form>
        <p class="auth-note">Already registered? <a href="login.php">Login here</a></p>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
