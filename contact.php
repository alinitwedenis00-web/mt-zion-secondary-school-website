<?php
require_once 'includes/functions.php';

$pageTitle = 'CONTACT US | MT ZION SECONDARY SCHOOL MUBENDE';
$message = '';
$reference = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['message'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $body === '') {
        $message = 'Please enter your name, a valid email address and your message.';
    } else {
        $reference = 'MZ' . date('ym') . strtoupper(bin2hex(random_bytes(4)));
        $insert = $pdo->prepare(
            "INSERT INTO contact_messages (name, email, subject, message, reference_code, status) "
            . "VALUES (?, ?, ?, ?, ?, 'Received')"
        );
        $pdo->beginTransaction();
        $insert->execute([$name, $email, $subject, $body, $reference]);
        $messageId = (int)$pdo->lastInsertId();
        $history = $pdo->prepare("INSERT INTO contact_message_history (message_id, old_status, new_status, reply) VALUES (?, NULL, 'Received', NULL)");
        $history->execute([$messageId]);
        $pdo->commit();
        $message = 'Your message has been received. Keep the reference number below to check its progress.';
    }
}

require 'includes/header.php';
?>
<section class="page-heading">
    <h1><?= e(setting('contact_heading', 'Get in Touch With Us')) ?></h1>
    <p><?= e(setting('contact_intro', 'We would love to hear from you.')) ?></p>
</section>
<section class="section contact-layout">
    <div>
        <h2>Contact Information</h2>
        <div class="contact-detail"><img src="assets/images/school/LOCATION.png" alt=""><div><h4>Our Address</h4><p><?= nl2br(e(setting('address', 'Mt Zion Secondary School, Mubende District, Uganda'))) ?></p></div></div>
        <div class="contact-detail"><img src="assets/images/school/CALL.png" alt=""><div><h4>Call Us</h4><p><?= e(setting('phone', '+256 772824530')) ?></p></div></div>
        <div class="contact-detail"><img src="assets/images/school/EMAIL.png" alt=""><div><h4>Email</h4><p><?= e(setting('email', 'mtzionssmubende2010@gmail.com')) ?></p></div></div>
        <div class="contact-map">
            <h2>Find Us on the Map</h2>
            <div class="map-frame"><iframe src="https://www.google.com/maps?q=MT+Zion+Secondary+School+Mubende&output=embed" title="Map showing Mt Zion Secondary School Mubende" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
            <a class="map-directions" href="https://www.google.com/maps?q=MT+Zion+Secondary+School+Mubende" target="_blank" rel="noopener noreferrer">Open location in Google Maps</a>
        </div>
    </div>
    <div>
        <h2>Send Us a Message</h2>
        <?php if ($message): ?><div class="success"><?= e($message) ?></div><?php endif; ?>
        <?php if ($reference): ?>
            <div class="notice"><strong>Your message reference: <?= e($reference) ?></strong><p>Visit <a href="message-track.php">Message Tracking</a> and enter this reference with your email address.</p></div>
        <?php endif; ?>
        <form method="post" class="form">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label>Your Name *<input name="name" placeholder="Enter your full name" required maxlength="150"></label>
            <label>Email Address *<input type="email" name="email" placeholder="Enter your email" required maxlength="190"></label>
            <label>Subject<input name="subject" placeholder="Subject of your message" maxlength="200"></label>
            <label>Message *<textarea name="message" rows="5" required></textarea></label>
            <button class="btn" type="submit">Send Message</button>
        </form>
        <img class="wide-photo lower-photo" src="assets/images/school/O BRIEFING.jpg" alt="Students studying">
    </div>
</section>
<?php require 'includes/footer.php'; ?>
