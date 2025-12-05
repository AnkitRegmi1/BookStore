<?php

function outputHeader(string $title): void
{
    // Ensure the session is started once
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Build auth-specific nav items
    if (isset($_SESSION['user_id'])) {
        $authItems = '
                <li><a href="cart.php">Cart</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="logout.php" class="btn-accent">Logout</a></li>';
    } else {
        $authItems = '
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn-accent">Register</a></li>';
    }

    // Detect current page and load appropriate CSS
    $currentPage = basename($_SERVER['PHP_SELF']);
    $pageCssMap = [
        'index.php' => 'home.css',
        'login.php' => 'auth.css',
        'register.php' => 'auth.css',
        'catalog.php' => 'catalog.css',
        'cart.php' => 'cart.css',
        'checkout.php' => 'checkout.css',
        'orders.php' => 'orders.css',
    ];
    
    $pageCss = isset($pageCssMap[$currentPage]) ? $pageCssMap[$currentPage] : null;
    $pageCssLink = $pageCss ? '<link rel="stylesheet" href="styles/' . $pageCss . '">' : '';

    // Clean HTML output via heredoc
    echo <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <!-- Global styles (always loaded) -->
    <link rel="stylesheet" href="styles/global.css">
    <!-- Page-specific styles -->
    {$pageCssLink}

    <script src="validation.js" defer></script>
    <script>
        // Hamburger Menu Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const mainNav = document.getElementById('main-nav');
            
            if (hamburgerBtn && mainNav) {
                hamburgerBtn.addEventListener('click', function() {
                    // Toggle nav-active class on main-nav
                    mainNav.classList.toggle('nav-active');
                    
                    // Toggle active class on hamburger button
                    hamburgerBtn.classList.toggle('active');
                    
                    // Update aria-expanded attribute
                    const isExpanded = hamburgerBtn.classList.contains('active');
                    hamburgerBtn.setAttribute('aria-expanded', isExpanded);
                });
                
                // Close menu when clicking on a link
                const navLinks = mainNav.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mainNav.classList.remove('nav-active');
                        hamburgerBtn.classList.remove('active');
                        hamburgerBtn.setAttribute('aria-expanded', 'false');
                    });
                });
            }
        });
    </script>
</head>
<body>
    <header class="navbar">
        <h1>SecondBook</h1>
        <button id="hamburger-btn" class="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <nav>
            <ul id="main-nav" class="nav-hidden">
                <li><a href="index.php">Home</a></li>
                <li><a href="catalog.php">Buy Books</a></li>{$authItems}
            </ul>
        </nav>
    </header>
    <main>
EOT;
}

function outputFooter(): void
{
    echo <<<EOT
    </main>
    <footer>
        <p>&copy; 2023 SecondBook Project</p>
    </footer>
</body>
</html>
EOT;
}

?>
