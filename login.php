<?php
include 'common.php';
include 'db_connect.php';

// Ensure session is started before reading/writing $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT user_id, password, address FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $username;
            // store address so checkout can show it
            $_SESSION['address'] = $row['address'] ?? '';
            header("Location: index.php"); exit;
        }
    }
    $msg = "Invalid credentials.";
}
outputHeader("Login");
?>
<div class="auth-split-layout">
    <div class="auth-image-side"></div>
    <div class="auth-form-side">
        <div class="auth-box">
            <h2>Login</h2>
            <p>Welcome back! Please login to your account.</p>
            
            <?php if (!empty($msg)): ?>
                <p style="color: #ff4d4d; background-color: rgba(255, 77, 77, 0.1); padding: 10px; border-radius: 4px; margin-bottom: 20px;"><?php echo $msg; ?></p>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">Username</span>
                        <input type="text" name="username" class="input-field-styled" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">Password</span>
                        <input type="password" name="password" class="input-field-styled" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-accent btn-full">Login</button>
            </form>
            
            <p class="auth-switch-link">Don't have an account? <a href="register.php">Create an account</a></p>
        </div>
    </div>
</div>
</body>
</html>