<?php
include 'common.php'; 
include 'db_connect.php';

// Ensure session is started before checking login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in (user must be logged in to checkout)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- 1. SERVER-SIDE PURCHASE LOGIC (Handles POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $error_msg = "";
    
    // Simple validation (must have cart items and credit card number)
    if (empty($_SESSION['cart'])) {
        $error_msg = "Your cart is empty. Please add items to checkout.";
    } elseif (empty($_POST['card_number'])) {
        $error_msg = "Please enter card details to complete the purchase.";
    } else {
        // Collect form data
        $user_id = $_SESSION['user_id'];
        $card_number = $_POST['card_number'];
        $card_expiry = $_POST['card_expiry'];
        $card_cvv = $_POST['card_cvv'];
        
        // Validate credit card expiry date
        if (!empty($card_expiry)) {
            $expiry_parts = explode('/', $card_expiry);
            if (count($expiry_parts) == 2) {
                $month = trim($expiry_parts[0]);
                $year_2digit = trim($expiry_parts[1]);
                
                // Validate month is between 01 and 12
                $month_num = intval($month);
                if ($month_num < 1 || $month_num > 12) {
                    $error_msg = "Invalid expiry month. Please enter a valid month (01-12).";
                } else {
                    // Convert 2-digit year to 4-digit year (assume 20xx)
                    $year_4digit = intval('20' . $year_2digit);
                    
                    // Get the last day of the expiry month
                    $last_day = date('t', mktime(0, 0, 0, $month_num, 1, $year_4digit));
                    $expiry_date = sprintf('%04d-%02d-%02d', $year_4digit, $month_num, $last_day);
                    
                    // Compare with current date
                    $current_date = date('Y-m-d');
                    if ($expiry_date < $current_date) {
                        $error_msg = "Your credit card has expired.";
                    }
                }
            }
        }
        
        // If there's an expiry validation error, stop processing
        if (empty($error_msg)) {
            // Build shipping address from form fields
            $street = trim($_POST['street_address'] ?? '');
            $apt = trim($_POST['apt_number'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $postal = trim($_POST['postal_code'] ?? '');
            
            // Build full address string
            $address = $street;
            if (!empty($apt)) {
                $address .= ', ' . $apt;
            }
            if (!empty($state) || !empty($postal)) {
                $address .= "\n";
                if (!empty($state)) {
                    $address .= $state;
                }
                if (!empty($state) && !empty($postal)) {
                    $address .= ' ';
                }
                if (!empty($postal)) {
                    $address .= $postal;
                }
            }
            
            // If no address fields provided, use saved address
            if (empty($street) && empty($state) && empty($postal)) {
                $address = $_SESSION['address'] ?? '';
            }
            $total_cost = 0;

            // Calculate total cost securely on the server
            foreach ($_SESSION['cart'] as $id => $qty) {
                // SECURITY NOTE: Use prepared statement if $id came from user input, but it's from $_SESSION
                $row = $conn->query("SELECT price FROM products WHERE product_id=$id")->fetch_assoc();
                $total_cost += $row['price'] * $qty;
            }
            $final_total = $total_cost * 1.08; // Include tax

            // --- 2. DATABASE TRANSACTION ---
            // Best practice to ensure either all or none of the inserts succeed.
            $conn->begin_transaction();
            
            try {
                // A. Insert into ORDERS table (Master Record)
                $order_date = date("Y-m-d H:i:s");
                // SECURITY: Use Prepared Statement for the order details
                $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total_amount, shipping_address) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isds", $user_id, $order_date, $final_total, $address);
                $stmt->execute();
                $order_id = $conn->insert_id;

                if (!$order_id) throw new Exception("Failed to create order record.");

                // B. Insert into ORDER_ITEMS table (Detail Records) and decrement inventory
                // Your table stores title + quantity; price is kept on the orders table
                $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_title, quantity) VALUES (?, ?, ?)");
                $update_inventory_stmt = $conn->prepare("UPDATE products SET book_quantity = book_quantity - ? WHERE product_id = ?");
                
                foreach ($_SESSION['cart'] as $product_id => $qty) {
                    $row = $conn->query("SELECT title, price FROM products WHERE product_id=$product_id")->fetch_assoc();
                    $item_title = $row['title'];

                    // SECURITY: Bind parameters for each item (order_id INT, title STRING, qty INT)
                    $item_stmt->bind_param("isi", $order_id, $item_title, $qty);
                    $item_stmt->execute();
                    
                    // Decrement inventory (use prepared statement for security)
                    $update_inventory_stmt->bind_param("ii", $qty, $product_id);
                    $update_inventory_stmt->execute();
                }
                $item_stmt->close();
                $update_inventory_stmt->close();
                
                // C. Insert into CREDIT_CARDS table (for project completion)
                // Table has columns: card_id, user_id, card_number, expiry
                $cc_stmt = $conn->prepare("INSERT INTO credit_cards (user_id, card_number, expiry) VALUES (?, ?, ?)");
                $cc_stmt->bind_param("iss", $user_id, $card_number, $card_expiry);
                $cc_stmt->execute();
                $cc_stmt->close();

                // All inserts succeeded. Commit the transaction.
                $conn->commit();
                
                // D. Clear the cart and redirect to success page
                unset($_SESSION['cart']);
                header("Location: checkout.php?status=success");
                exit;

            } catch (Exception $e) {
                // Something went wrong. Rollback and set error message.
                $conn->rollback();
                $error_msg = "Transaction failed: " . $e->getMessage() . " Please try again.";
            }
        }
    }
}

// --- 3. HTML PRESENTATION ---

outputHeader("Checkout");

// Check for success status
if (isset($_GET['status']) && $_GET['status'] == 'success'):
?>
    <section class="success-page">
        <h2>Purchase Complete!</h2>
        <p>Thank you for shopping with SecondBook. Your order has been placed and confirmed.</p>
        <?php 
        // Get the last order's shipping address
        $uid = $_SESSION['user_id'];
        $lastOrder = $conn->query("SELECT shipping_address FROM orders WHERE user_id=$uid ORDER BY order_date DESC LIMIT 1");
        $shippingAddress = '';
        if ($lastOrder && $row = $lastOrder->fetch_assoc()) {
            $shippingAddress = $row['shipping_address'];
        }
        ?>
        <p class="summary">Your books will be shipped to:<br><strong><?= nl2br(htmlspecialchars($shippingAddress)) ?></strong></p>
        <a href='catalog.php' class='btn-accent btn-checkout'>Continue Shopping</a>
    </section>

<?php 
// Check for error message
elseif (isset($error_msg) && !empty($error_msg)): 
?>
    <p style="color: red; font-weight: bold;">Error: <?= $error_msg ?></p>

<?php 
// Display empty cart message
elseif (empty($_SESSION['cart'])): 
?>
    <p>Your cart is empty. <a href='catalog.php'>Shop here!</a></p>

<?php 
else: 
    // Calculate totals for display
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        $row = $conn->query("SELECT price FROM products WHERE product_id=$id")->fetch_assoc();
        $total += $row['price'] * $qty;
    }
    $final_total = $total * 1.08; 
    $tax = $final_total - $total;
?>

<div class="checkout-layout">

    <section class="checkout-forms">
        
        <form action="checkout.php" method="post" id="checkout-form" novalidate>

            <div class="form-section">
                <h3>1. Shipping Details</h3>
                <div class="address-box">
                    <p><strong>Ship To:</strong></p>
                    <div id="address-display">
                        <?php 
                        $savedAddress = $_SESSION['address'] ?? '';
                        if (!empty($savedAddress)) {
                            echo '<p id="address-text">' . nl2br(htmlspecialchars($savedAddress)) . '</p>';
                        } else {
                            echo '<p id="address-text">No address found. Please enter your shipping address.</p>';
                        }
                        ?>
                        <a href="#" id="change-address-link" style="font-size: 0.9rem; color: var(--accent-primary); text-decoration: none;">Change</a>
                    </div>
                    <div id="address-edit" style="display: <?= empty($savedAddress) ? 'block' : 'none' ?>;">
                        <div class="form-group">
                            <label for="street_address">Street Address *</label>
                            <input type="text" id="street_address" name="street_address" required placeholder="123 Main Street">
                        </div>
                        <div class="form-group">
                            <label for="apt_number">Apartment, suite, etc. (Optional)</label>
                            <input type="text" id="apt_number" name="apt_number" placeholder="Apt 4B">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="state">State *</label>
                                <input type="text" id="state" name="state" required placeholder="CA">
                            </div>
                            <div class="form-group">
                                <label for="postal_code">Postal Code *</label>
                                <input type="text" id="postal_code" name="postal_code" required placeholder="12345">
                            </div>
                        </div>
                        <div>
                            <a href="#" id="save-address-link" style="font-size: 0.9rem; color: var(--accent-primary); text-decoration: none; margin-right: 15px;">Save</a>
                            <a href="#" id="cancel-address-link" style="font-size: 0.9rem; color: var(--text-secondary); text-decoration: none;">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>2. Payment Information</h3>
                
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" required placeholder="xxxx xxxx xxxx xxxx">
                    <small class="error-message" id="card-error" style="display: none; color: #ff4d4d; font-size: 0.85rem; margin-top: 5px;"></small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="card_expiry">Expiry Date</label>
                        <input type="text" id="card_expiry" name="card_expiry" required pattern="\d{2}/\d{2}" title="MM/YY" placeholder="MM/YY">
                    </div>
                    <div class="form-group">
                        <label for="card_cvv">CVV</label>
                        <input type="text" id="card_cvv" name="card_cvv" required pattern="\d{3,4}" title="3 or 4 digits" placeholder="123">
                    </div>
                </div>
            </div>
        </form>

    </section>

    <aside class="checkout-summary-sidebar">
        <div class="summary-card">
            <h3>Order Summary</h3>

            <div class="checkout-items-list">
                <?php foreach ($_SESSION['cart'] as $id => $qty): 
                    // Need to fetch image and title again for display
                    $item_row = $conn->query("SELECT title, price, image_url FROM products WHERE product_id=$id")->fetch_assoc();
                ?>
                    <div class="checkout-item">
                        <img src="images/<?= $item_row['image_url'] ?>" alt="<?= $item_row['title'] ?>">
                        <div class="item-info">
                            <p class="title"><?= $item_row['title'] ?></p>
                            <p class="qty">Qty: <?= $qty ?></p>
                        </div>
                        <p class="price">$<?= number_format($item_row['price'] * $qty, 2) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-totals">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (8%)</span>
                    <span>$<?= number_format($tax, 2) ?></span>
                </div>
                <div class="summary-row total">
                    <span>Order Total</span>
                    <span>$<?= number_format($final_total, 2) ?></span>
                </div>
            </div>

            <button type="submit" form="checkout-form" class="btn-accent btn-block btn-checkout">Complete Purchase</button>
            
            <p style="text-align: center; margin-top: 15px; font-size: 0.9rem; color: var(--text-secondary);">
                🔒 Secure Checkout
            </p>
        </div>
    </aside>

</div> 

<script>
// Simple input masking for card number and expiry
(function () {
    const cardInput = document.getElementById('card_number');
    const expiryInput = document.getElementById('card_expiry');

    if (cardInput) {
        const cardError = document.getElementById('card-error');
        
        cardInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            value = value.slice(0, 16);
            const parts = [];
            for (let i = 0; i < value.length; i += 4) {
                parts.push(value.slice(i, i + 4));
            }
            e.target.value = parts.join(' ');
            
            // Clear error when typing
            if (cardError) {
                cardError.style.display = 'none';
                cardError.textContent = '';
            }
            
            // Remove invalid state
            e.target.setCustomValidity('');
        });
        
        // Form submission validation
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function (e) {
                const digitsOnly = cardInput.value.replace(/[^0-9]/g, '');
                if (digitsOnly.length !== 16) {
                    e.preventDefault();
                    cardInput.setCustomValidity('Card number must be exactly 16 digits');
                    if (cardError) {
                        cardError.textContent = 'Card number must be exactly 16 digits';
                        cardError.style.display = 'block';
                    }
                    cardInput.focus();
                    return false;
                } else {
                    cardInput.setCustomValidity('');
                    if (cardError) {
                        cardError.style.display = 'none';
                    }
                }
            });
        }
        
        // Validate on blur
        cardInput.addEventListener('blur', function (e) {
            const digitsOnly = e.target.value.replace(/[^0-9]/g, '');
            if (digitsOnly.length > 0 && digitsOnly.length !== 16) {
                e.target.setCustomValidity('Card number must be exactly 16 digits');
                if (cardError) {
                    cardError.textContent = 'Card number must be exactly 16 digits';
                    cardError.style.display = 'block';
                }
            } else {
                e.target.setCustomValidity('');
                if (cardError) {
                    cardError.style.display = 'none';
                }
            }
        });
    }

    if (expiryInput) {
        expiryInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            value = value.slice(0, 4);
            if (value.length >= 3) {
                let month = value.slice(0, 2);
                let year = value.slice(2);
                const monthNum = parseInt(month, 10);
                if (!isNaN(monthNum)) {
                    if (monthNum < 1) month = '01';
                    if (monthNum > 12) month = '12';
                }
                value = month + '/' + year;
            }
            e.target.value = value;
        });
    }

    // Address change functionality
    const changeLink = document.getElementById('change-address-link');
    const saveLink = document.getElementById('save-address-link');
    const cancelLink = document.getElementById('cancel-address-link');
    const addressDisplay = document.getElementById('address-display');
    const addressEdit = document.getElementById('address-edit');
    const addressText = document.getElementById('address-text');
    const streetInput = document.getElementById('street_address');
    const aptInput = document.getElementById('apt_number');
    const stateInput = document.getElementById('state');
    const postalInput = document.getElementById('postal_code');
    
    // Store original values when page loads
    const originalValues = {
        street: streetInput ? streetInput.value : '',
        apt: aptInput ? aptInput.value : '',
        state: stateInput ? stateInput.value : '',
        postal: postalInput ? postalInput.value : ''
    };

    // Function to format address from fields
    function formatAddress(street, apt, state, postal) {
        let addr = street;
        if (apt) {
            addr += ', ' + apt;
        }
        if (state || postal) {
            addr += '\n';
            if (state) {
                addr += state;
            }
            if (state && postal) {
                addr += ' ';
            }
            if (postal) {
                addr += postal;
            }
        }
        return addr;
    }

    if (changeLink && addressDisplay && addressEdit) {
        changeLink.addEventListener('click', function(e) {
            e.preventDefault();
            addressDisplay.style.display = 'none';
            addressEdit.style.display = 'block';
            if (streetInput) {
                streetInput.focus();
            }
        });
    }

    if (saveLink && addressText) {
        saveLink.addEventListener('click', function(e) {
            e.preventDefault();
            const street = streetInput ? streetInput.value.trim() : '';
            const state = stateInput ? stateInput.value.trim() : '';
            const postal = postalInput ? postalInput.value.trim() : '';
            
            if (street && state && postal) {
                const apt = aptInput ? aptInput.value.trim() : '';
                const formattedAddress = formatAddress(street, apt, state, postal);
                // Update the display text
                addressText.innerHTML = formattedAddress.replace(/\n/g, '<br>');
                // Hide edit, show display
                addressEdit.style.display = 'none';
                addressDisplay.style.display = 'block';
            } else {
                alert('Please fill in all required address fields (Street Address, State, and Postal Code)');
            }
        });
    }

    if (cancelLink) {
        cancelLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Restore original values
            if (streetInput) streetInput.value = originalValues.street;
            if (aptInput) aptInput.value = originalValues.apt;
            if (stateInput) stateInput.value = originalValues.state;
            if (postalInput) postalInput.value = originalValues.postal;
            // Hide edit, show display
            addressEdit.style.display = 'none';
            addressDisplay.style.display = 'block';
        });
    }
})();
</script>

<?php 
endif;

outputFooter();
?>