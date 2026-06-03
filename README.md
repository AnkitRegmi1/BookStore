#  BookStore – Full-Stack E-Commerce Web Application

A full-stack bookstore web application built with **PHP, MySQL, JavaScript, HTML, and CSS**. The platform allows users to browse books, manage a shopping cart, securely create accounts, place orders, and view purchase history through a complete end-to-end e-commerce workflow.

##  Features

### User Authentication

* User registration and login system
* Password hashing and verification
* Session-based authentication
* Protected checkout and order history pages

### Product Catalog

* Browse available books
* View book details including title, author, price, description, and inventory
* Search and explore products

### Shopping Cart

* Add books to cart
* Update quantities
* Remove items
* Session-based cart persistence

### Checkout System

* Secure checkout workflow
* Shipping address collection
* Payment information processing
* Order confirmation

### Order Management

* View previous purchases
* Track order history
* Store order details and purchased items

### Inventory Management

* Automatic inventory updates after purchase
* Prevent overselling by maintaining available stock counts

---

##  System Architecture

```text
Browser
   ↓
PHP Application
   ↓
MySQL Database
```

The application follows a traditional server-rendered architecture where PHP handles both backend logic and dynamic HTML generation.

### Core Components

#### Catalog

Displays all available books by retrieving product information from the database.

#### Cart

Stores selected products in PHP sessions until checkout is completed.

#### Authentication

Handles user registration, login, session management, and access control.

#### Checkout

Processes orders, validates information, updates inventory, and records transactions.

#### Orders

Displays purchase history and previously completed transactions.

---

## Purchase Workflow

```text
User browses catalog
        ↓
Adds books to cart
        ↓
Cart stored in PHP session
        ↓
User logs in
        ↓
Checkout form submitted
        ↓
Server validates order
        ↓
Order created in database
        ↓
Order items recorded
        ↓
Inventory updated
        ↓
Transaction committed
        ↓
Cart cleared
        ↓
Order confirmation displayed
```

---

## Security Considerations

### Authentication

* Session-based user authentication
* Password hashing and verification
* Protected routes for checkout and order management

### SQL Injection Prevention

* Prepared statements used for authentication and database inserts
* Parameterized queries help separate SQL logic from user input

### Cross-Site Scripting (XSS) Protection

* User-generated content is escaped before rendering
* `htmlspecialchars()` used to prevent execution of malicious scripts

### Transaction Management

Checkout operations use database transactions to ensure:

* Order creation succeeds completely
* Inventory updates remain consistent
* Failed operations are rolled back automatically

---

## 🗄️ Database Design

### Users

Stores registered user accounts.

### Products

Stores book information including inventory levels.

### Orders

Stores completed purchases.

### Order Items

Stores individual products associated with an order.

Relationships:

```text
Users
  │
  └── Orders
          │
          └── Order Items
                    │
                    └── Products
```

---

## Technologies Used

### Backend

* PHP
* MySQL

### Frontend

* HTML
* CSS
* JavaScript

### Authentication

* PHP Sessions
* Password Hashing

### Database

* MySQL

---

## 🎯 Learning Outcomes

Through this project I gained experience with:

* Full-stack web development
* Relational database design
* User authentication and session management
* Shopping cart implementation
* Checkout and transaction workflows
* SQL query development
* Secure web application practices
* CRUD operations
* E-commerce application architecture

---

## Future Improvements

* Payment gateway integration (Stripe/PayPal)
* Admin dashboard
* Product search and filtering
* User profile management
* Email order confirmations
* Responsive mobile-first redesign
* REST API version of the application

---

Developed as a full-stack web application to explore core e-commerce concepts, secure authentication, database design, and transactional workflows using PHP and MySQL.
