<?php 
include 'common.php'; 
include 'db_connect.php'; 

outputHeader("Home"); 

// Logic: Get 5 products for the 'Best Sellers' section
$res = $conn->query("SELECT * FROM products LIMIT 5"); 
?>

<script src="js_carousel.js" defer></script> 

<!-- Full-page hero carousel -->
<section class="hero-carousel">
    <div id="book-carousel" class="hero-carousel-track">
        <!-- Slide 1: Sapiens -->
        <article class="book-slide book-slide--sapiens" data-title="Sapiens">
            <div class="hero-text">
                <div class="book-slide-content">
                    <h2 class="book-title">Sapiens</h2>
                    <p class="book-author">Yuval Noah Harari</p>
                    <p class="book-description">
                        A groundbreaking exploration of humanity's journey, from early hominids to the powerful, complex 
                        beings we are today. Harari examines the biological and historical forces that have shaped our 
                        species and the potential paths for our future.
                    </p>
                    <a href="cart.php?action=add&id=1" class="btn-accent hero-cta">Buy Now</a>
                </div>
            </div>
            <div class="hero-image-container">
                <img src="images/sapiens_cover.jpg" alt="Sapiens Book Cover">
            </div>
        </article>

        <!-- Slide 2: To Kill a Mockingbird -->
        <article class="book-slide book-slide--mockingbird" data-title="To Kill a Mockingbird">
            <div class="hero-text">
                <div class="book-slide-content">
                    <h2 class="book-title">To Kill a Mockingbird</h2>
                    <p class="book-author">Harper Lee</p>
                    <p class="book-description">
                        Set in the American South during the 1930s, this novel follows the young Scout Finch as her father, 
                        attorney Atticus Finch, defends a man unjustly accused of a crime. It is a powerful exploration of 
                        racial injustice, innocence, and courage.
                    </p>
                    <a href="cart.php?action=add&id=2" class="btn-accent hero-cta">Buy Now</a>
                </div>
            </div>
            <div class="hero-image-container">
                <img src="images/To_kill_a_mocking_bird_cover.jpg" alt="To Kill a Mockingbird Book Cover">
            </div>
        </article>

        <!-- Slide 3: Pachinko -->
        <article class="book-slide book-slide--pachinko" data-title="Pachinko">
            <div class="hero-text">
                <div class="book-slide-content">
                    <h2 class="book-title">Pachinko</h2>
                    <p class="book-author">Min Jin Lee</p>
                    <p class="book-description">
                        An epic saga following four generations of a Korean family who immigrate to Japan, facing poverty, 
                        discrimination, and questions of identity, all centered around the life of the determined Sunja.
                    </p>
                    <a href="cart.php?action=add&id=3" class="btn-accent hero-cta">Buy Now</a>
                </div>
            </div>
            <div class="hero-image-container">
                <img src="images/Pachinko_cover.jpg" alt="Pachinko Book Cover">
            </div>
        </article>
    </div>

    <!-- Optional navigation controls -->
    <button class="hero-nav hero-nav--prev" aria-label="Previous book">&#10094;</button>
    <button class="hero-nav hero-nav--next" aria-label="Next book">&#10095;</button>

    <div class="hero-dots" aria-label="Carousel navigation">
        <button class="hero-dot hero-dot--active" data-index="0"></button>
        <button class="hero-dot" data-index="1"></button>
        <button class="hero-dot" data-index="2"></button>
    </div>
</section>

<hr>

<section class="bestseller-month-section">
    <div class="bestseller-month-header">
        <h2 class="bestseller-month-title">Best Seller of the Month</h2>
        <a href="catalog.php" class="bestseller-month-see-more">See more</a>
    </div>

    <div class="bestseller-month-scroller">
        <?php while ($row = $res->fetch_assoc()): ?>
            <article class="bestseller-card">
                <div class="bestseller-card-image">
                    <img src="images/<?= $row['image_url'] ?>" alt="<?= $row['title'] ?> Cover">
                </div>
                <div class="bestseller-card-body">
                    <h3 class="bestseller-card-title"><?= $row['title'] ?></h3>
                    <p class="bestseller-card-author"><?= $row['author'] ?></p>
                    <a href="cart.php?action=add&id=<?= $row['product_id'] ?>" class="btn-add bestseller-card-add">Add to Cart</a>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</section>

<?php outputFooter(); ?>