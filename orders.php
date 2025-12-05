<?php
// Start session FIRST before including any files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'common.php'; 
include 'db_connect.php';

// Redirect if not logged in (must be after session_start)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

outputHeader("Order History");

$uid = $_SESSION['user_id'];
$res = $conn->query("SELECT * FROM orders WHERE user_id=$uid ORDER BY order_date DESC");

?>

<?php if ($res->num_rows == 0): ?>
    <div class="empty-orders">
        <h2>My Orders</h2>
        <p>You do not have any products to show.</p>
        <a href="catalog.php" class="btn-accent btn-empty-orders">Start Shopping</a>
    </div>
<?php else: ?>
    <h2>My Orders</h2>
    <div class="orders-list">
        <?php while ($row = $res->fetch_assoc()): 
            $oid = $row['order_id'];
            $items = $conn->query("SELECT * FROM order_items WHERE order_id=$oid");
        ?>
            <article class="order-card">
                <div class="order-header">
                    <h3>Order #<?= $row['order_id'] ?></h3>
                    <p class="order-date"><?= date("F j, Y", strtotime($row['order_date'])) ?></p>
                </div>
                
                <div class="order-details">
                    <p class="shipping-address"><strong>Shipped to:</strong> <?= htmlspecialchars($row['shipping_address']) ?></p>
                </div>
                
                <div class="order-items">
                    <h4>Items:</h4>
                    <ul>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <li><?= $item['quantity'] ?>x <?= htmlspecialchars($item['product_title']) ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                
                <div class="order-total">
                    <strong>Total: <span class="price">$<?= number_format($row['total_amount'], 2) ?></span></strong>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php outputFooter(); ?>