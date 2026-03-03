# Lake Victoria Tilapia Depot

> A full-stack e-commerce web application for ordering fresh tilapia fish online — built with PHP, MySQL, and Tailwind CSS.

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Project Overview

Lake Victoria Tilapia Depot is a production-ready e-commerce platform designed to streamline the sale and delivery of fresh tilapia fish sourced from Lake Victoria, Kenya. The system serves three distinct user roles — **customer**, **staff**, and **admin** — each with a tailored experience.

**Problem it solves:** Traditional fish markets rely entirely on in-person sales. This application brings the market online, enabling customers to browse products, place orders, and pay securely via M-PESA mobile money — without visiting the physical location.

---

## Technologies Used

| Layer        | Technology                                 |
| ------------ | ------------------------------------------ |
| Backend      | PHP 7.4+, PDO (MySQL)                      |
| Database     | MySQL 5.7+                                 |
| Frontend     | HTML5, Tailwind CSS 3.x, JavaScript (ES6+) |
| Payments     | Safaricom M-PESA Daraja API (STK Push)     |
| Email        | PHPMailer (SMTP / Gmail)                   |
| SMS          | Africa's Talking API                       |
| Architecture | MVC (Model-View-Controller)                |
| Icons/Fonts  | Font Awesome 6, Google Fonts (Poppins)     |
| Dev Tools    | XAMPP, Composer, phpMyAdmin                |

---

## Features

### Customer Portal

- Secure registration and login with role-based session management
- Product catalogue with search and filter functionality
- Shopping cart — add, update quantities, and remove items
- Streamlined checkout with delivery address input
- **M-PESA STK Push** — real-time mobile payment initiation
- Order tracking with live status updates
- Order history and re-order capability
- Profile management

### Staff Dashboard

- Analytics overview — revenue, orders, and stock metrics
- Full product (fish) management — add, edit, delete, with image uploads
- Inventory tracking with low-stock alerts
- Order management — update status across lifecycle (pending → processing → completed/cancelled)
- Sales reporting (daily, weekly, monthly)

### Admin Controls

- User management — create, edit, and delete accounts
- Role assignment (customer, staff, admin)
- Full system access and reporting

### Public-Facing Pages

- Responsive landing page with service sections, pricing, and contact form
- About, Services, Products, Contact, and Terms pages

---

## Project Structure

```
lake-victoria-tilapia-depot/
├── config/
│   ├── config.php           # App, DB, M-PESA, SMTP, and SMS settings
│   └── database.php         # PDO database connection
├── controllers/
│   ├── AuthController.php   # Login, registration, session handling
│   ├── CartController.php   # Cart logic
│   ├── FishController.php   # Product CRUD
│   ├── MpesaController.php  # M-PESA STK Push & callback handling
│   ├── MpesaHelper.php      # M-PESA token + request helpers
│   ├── NotificationService.php # Email & SMS dispatch
│   ├── OrderController.php  # Order lifecycle management
│   └── UserController.php   # User management
├── models/
│   ├── cart.php             # Cart data operations
│   ├── fish.php             # Fish/product data operations
│   ├── order.php            # Order data operations
│   └── user.php             # User data operations
├── handlers/
│   ├── auth_handler.php     # Form POST handler for auth
│   └── cart_handler.php     # Form POST handler for cart actions
├── views/
│   ├── auth/                # Login and register pages
│   ├── customer/            # Customer-facing pages
│   ├── staff/               # Staff dashboard pages
│   ├── public/              # Public info pages
│   └── includes/            # Shared header, footer, navbar, sidebar
├── callback/
│   └── mpesa_callback.php   # M-PESA payment result webhook
├── database/
│   ├── schema.sql           # Initial database schema
│   └── update_schema.sql    # Incremental schema updates
├── uploads/                 # Product images (writable directory)
├── vendor/                  # Composer dependencies (PHPMailer)
├── index.php                # Entry point → redirects to landing.php
├── landing.php              # Public landing page
└── composer.json
```

---

## Setup Instructions

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL)
- PHP 7.4+
- [Composer](https://getcomposer.org/) (for PHPMailer)

### 1. Clone the Repository

```bash
git clone https://github.com/vanessaNjoroge2/Lake_Victoria_Tilapia-_Depot.git
```

Place the folder inside your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\lake-victoria-tilapia-depot\
```

### 2. Install Dependencies

```bash
cd lake-victoria-tilapia-depot
composer install
```

### 3. Create the Database

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Create a new database named `lake_victoria_tilapia_depot`.
4. Import `database/schema.sql` via the **SQL** tab.
5. Then import `database/update_schema.sql` to apply all schema updates.

### 4. Configure the Application

Edit `config/config.php`:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'lake_victoria_tilapia_depot');
define('DB_USER', 'root');
define('DB_PASS', '');

// M-PESA (get credentials from https://developer.safaricom.co.ke/)
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_SHORTCODE', 'your_shortcode');
define('MPESA_PASSKEY', 'your_passkey');
define('MPESA_ENVIRONMENT', 'sandbox'); // change to 'production' when live

// Email (SMTP / PHPMailer)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'your_email@gmail.com');
define('MAIL_PASSWORD', 'your_app_password');
```

### 5. Verify File Permissions

Ensure the `uploads/` directory is writable:

```bash
# Linux / macOS
chmod 755 uploads/
```

---

## Usage

Visit the application in your browser:

```
http://localhost/lake-victoria-tilapia-depot/
```

### Demo Accounts

| Role     | Username    | Password   | Access Level                          |
| -------- | ----------- | ---------- | ------------------------------------- |
| Admin    | `admin`     | `password` | Full access — users, orders, settings |
| Staff    | `staff1`    | `password` | Products, orders, dashboard           |
| Customer | `customer1` | `password` | Browse, cart, checkout, order history |

> **Important:** Change all default passwords before deploying to production.

### Key URLs

| Page             | URL                               |
| ---------------- | --------------------------------- |
| Landing Page     | `/`                               |
| Login            | `/views/auth/login.php`           |
| Register         | `/views/auth/register.php`        |
| Browse Products  | `/views/customer/browse_fish.php` |
| Shopping Cart    | `/views/customer/cart.php`        |
| Checkout         | `/views/customer/checkout.php`    |
| Staff Dashboard  | `/views/staff/dashboard.php`      |
| Order Management | `/views/staff/orders.php`         |
| User Management  | `/views/staff/users_list.php`     |

---

## Security Highlights

- Passwords hashed with **bcrypt** (`password_hash`)
- SQL injection prevention via **PDO prepared statements**
- XSS protection through **input sanitization and output escaping**
- Role-based route guards enforced on every protected page
- File upload validation — type and size restrictions
- CSRF token protection on sensitive forms

---

## Potential Extensions

- Add **PayPal / Stripe** as alternative payment gateways
- Implement **product ratings and reviews**
- Build a **REST API** for a future mobile application (React Native / Flutter)
- Add **real-time notifications** using WebSockets
- Integrate **Google Maps API** for delivery tracking
- Introduce a **loyalty / referral rewards** programme

---

## Author

**Vanessa Njoroge**  
[GitHub Profile](https://github.com/vanessaNjoroge2)

---

## License

This project is licensed under the [MIT License](LICENSE).
