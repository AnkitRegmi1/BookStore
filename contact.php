<?php 
include 'common.php'; 

$msg = "";
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic server-side security/validation (always required)
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['msg']);
    
    if (empty($email) || empty($message)) {
        $msg = "Error: Please fill out all fields.";
    } else {
        // In a real application, you would send an email here.
        // For this project, we just acknowledge receipt.
        $msg = "Thank you! Your message has been received.";
    }
}

outputHeader("Contact"); 
?>

<h2>Contact Us</h2>

<?php if (!empty($msg)): ?>
    <p style="color: green; font-weight: bold;"><?= $msg ?></p>
<?php endif; ?>

<form action="contact.php" method="post" novalidate>
    <label>Email:</label><br><input type="email" name="email" required><br>
    <label>Message:</label><br><textarea name="msg" required></textarea><br>
    <button type="submit">Send Message</button>
</form>

<?php outputFooter(); ?>