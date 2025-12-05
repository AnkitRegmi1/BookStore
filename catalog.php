<?php
include 'common.php'; 
include 'db_connect.php';

outputHeader("Catalog");

// Fetch all products
$res = $conn->query("SELECT product_id, title, author, price, image_url, description FROM products ORDER BY title ASC"); 
?>

<h2>Book Catalog</h2>

<div class="product-grid">
    <?php 
    while ($row = $res->fetch_assoc()): 
    ?>
        <article class="product-card">
            <div class="card-image">
                <img 
                    src="images/<?= $row['image_url'] ?>" 
                    alt="<?= $row['title'] ?> Cover"
                >
            </div>
            <div class="card-content">
                <h3><?= $row['title'] ?></h3>
                <p class="author">by <?= $row['author'] ?></p>
                
                <p class="description"><?= $row['description'] ?? 'A compelling read that will captivate you from the first page.' ?></p>

                <div class="card-footer">
                    <span class="price">$<?= number_format($row['price'], 2) ?></span>
                    
                    <a href="cart.php?action=add&id=<?= $row['product_id'] ?>" class="btn-add" aria-label="Add to Cart">
                        Add +
                    </a>
                </div>
            </div>
        </article>
    <?php 
    endwhile; 
    ?>
</div>

<?php outputFooter(); ?>