<?php
$pageTitle = 'Contact | Lumiere Books';
require_once 'includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // contact message එක admin panel එකෙන් බලන්න database එකට save කරයි.
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        setFlash('success', 'Thank you. Your message has been received.');
    } else {
        setFlash('error', 'Please complete all fields.');
    }
    redirect('contact.php');
}
?>
<section class="page-hero slim"><div class="container"><span class="eyebrow">Contact</span><h1>We are happy to help</h1></div></section>
<section class="container section-pad contact-grid">
    <form method="post" class="contact-form">
        <h3>Send Message</h3>
        <label>Name</label><input type="text" name="name" required>
        <label>Email</label><input type="email" name="email" required>
        <label>Message</label><textarea name="message" rows="5" required></textarea>
        <button class="btn" type="submit">Send Message</button>
    </form>
    <div class="contact-info">
        <h3>Store Details</h3>
        <p><strong>Address:</strong> 45, Book Street, Colombo, Sri Lanka</p>
        <p><strong>Phone:</strong> +94 77 123 4567</p>
        <p><strong>Email:</strong> hello@lumierebooks.lk</p>
        <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570b?auto=format&fit=crop&w=700&q=80" alt="Real library shelves">
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
