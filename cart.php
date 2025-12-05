<?php
include 'common.php'; 
include 'db_connect.php';

// Ensure session is started before checking login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in (User must be logged in to view cart)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// --- Handle Cart Actions ---
if (isset($_GET['action'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'add') $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    if ($_GET['action'] == 'remove') unset($_SESSION['cart'][$id]);
    if ($_GET['action'] == 'empty') unset($_SESSION['cart']);
    if ($_GET['action'] == 'increase') {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        }
    }
    if ($_GET['action'] == 'decrease') {
        if (isset($_SESSION['cart'][$id]) && $_SESSION['cart'][$id] > 1) {
            $_SESSION['cart'][$id]--;
        } elseif (isset($_SESSION['cart'][$id]) && $_SESSION['cart'][$id] == 1) {
            unset($_SESSION['cart'][$id]);
        }
    }
    header("Location: cart.php"); 
    exit;
}

outputHeader("Your Cart");
?>

<?php if (empty($_SESSION['cart'])): ?>
    <div class="empty-cart">
        <h2>Your Shopping Cart</h2>
        <p>Your cart is empty.</p>
        <a href='catalog.php' class="btn-accent btn-empty-cart">Go Shop!</a>
    </div>

<?php else: 
    $total = 0; 
?>

<div class="cart-layout">

    <section class="cart-items-list">
        <?php 
        foreach ($_SESSION['cart'] as $id => $qty): 
            // UPDATED QUERY: Fetch image and author too
            $row = $conn->query("SELECT title, author, price, image_url FROM products WHERE product_id=$id")->fetch_assoc();
            $sub = $row['price'] * $qty;
            $total += $sub;
        ?>
            <article class="cart-item">
                <div class="item-image">
                    <img src="images/<?= $row['image_url'] ?>" alt="<?= $row['title'] ?>">
                </div>
                
                <div class="item-details">
                    <h3><?= $row['title'] ?></h3>
                    <p class="author">by <?= $row['author'] ?></p>
                </div>

                <div class="item-quantity">
                    <div class="quantity-controls">
                        <a href='cart.php?action=decrease&id=<?= $id ?>' class="qty-btn">−</a>
                        <span class="qty-value"><?= $qty ?></span>
                        <a href='cart.php?action=increase&id=<?= $id ?>' class="qty-btn">+</a>
                    </div>
                </div>

                <div class="item-actions">
                    <span class="price">$<?= number_format($sub, 2) ?></span>
                    <a href='cart.php?action=remove&id=<?= $id ?>' class="remove-link" aria-label="Remove item">Remove</a>
                </div>
            </article>
        <?php endforeach; ?>
        <div class="empty-cart-container">
            <a href='cart.php?action=empty' class="btn-outline-danger">Empty Entire Cart</a>
        </div>
    </section>

    <aside class="cart-summary-sidebar">
        <div class="summary-card">
            <h3>Order Summary</h3>
            
            <?php 
            $tax_rate = 0.08;
            $tax_amount = $total * $tax_rate;
            $final_total = $total + $tax_amount;
            ?>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Tax (8%)</span>
                <span>$<?= number_format($tax_amount, 2) ?></span>
            </div>
            
            <div class="summary-row total">
                <span>Total</span>
                <span>$<?= number_format($final_total, 2) ?></span>
            </div>

            <!-- Updated: use PayPal payment flow instead of local checkout form -->
            <a href='create-payment.php' class="btn-accent btn-block btn-checkout">Proceed to Checkout</a>
            <br>
            <a href="catalog.php" style="color: var(--text-secondary); text-decoration: none; display: block; text-align: center; margin-top: 10px;">Continue Shopping</a>
        </div>
    </aside>

</div> 

<?php endif; 

outputFooter();
?>