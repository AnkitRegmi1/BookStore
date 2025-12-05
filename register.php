<?php
include 'common.php';
include 'db_connect.php';

outputHeader("Register");

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = htmlspecialchars($_POST['email']);
    
    // Build address from separate fields
    $street = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    
    // Combine into full address string
    $addressParts = [];
    if (!empty($street)) $addressParts[] = $street;
    if (!empty($city)) $addressParts[] = $city;
    if (!empty($state)) $addressParts[] = $state;
    $address = htmlspecialchars(implode(', ', $addressParts));

    // Server-side validation
    if (empty($username) || strlen($password) < 8) {
        $msg = "Invalid input";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $address);

        if ($stmt->execute()) {
            $msg = "User created! <a href='login.php'>Login</a>";
        } else {
            $msg = "Error: " . $conn->error;
        }
    }
}
?>
<div class="auth-split-layout">
    <div class="auth-image-side"></div>
    <div class="auth-form-side">
        <div class="auth-box">
            <h2>Create an Account</h2>
            <p>Sign up to buy your favorite books</p>
            
            <?php if (!empty($msg)): ?>
                <div class="register-message" style="color: <?php echo strpos($msg, 'created') !== false ? '#ffffff' : '#ff4d4d'; ?>; margin-bottom: 20px;">
                    <?php 
                    if (strpos($msg, 'created') !== false) {
                        // Replace the link with styled version
                        echo str_replace('<a href=\'login.php\'>Login</a>', '<a href="login.php" style="color: var(--accent-primary); text-decoration: none; font-weight: bold;">Login</a>', $msg);
                    } else {
                        echo $msg;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="POST" novalidate>
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">Username</span>
                        <input type="text" name="username" class="input-field-styled" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">Password</span>
                        <input type="password" name="password" class="input-field-styled" placeholder="Min 8 characters" required>
                    </div>
                </div>
                
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">Email</span>
                        <input type="email" name="email" class="input-field-styled" placeholder="example@mail.com" required>
                    </div>
                </div>
                
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">Address</span>
                        <input type="text" name="street_address" class="input-field-styled" placeholder="123 Main Street" required>
                    </div>
                </div>
                
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">City</span>
                        <input type="text" name="city" class="input-field-styled" placeholder="Enter your city" required>
                    </div>
                </div>
                
                <div class="input-group-wrapper">
                    <div class="input-group">
                        <span class="input-label-box">State</span>
                        <input type="text" name="state" class="input-field-styled" placeholder="Enter your state" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-accent btn-full">Create Account</button>
            </form>
            
            <p class="auth-switch-link">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>
</body>
</html>