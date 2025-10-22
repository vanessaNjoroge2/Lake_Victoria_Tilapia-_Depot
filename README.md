📁 Project Structure
text
lake-victoria-tilapia-depot/
│
├── config/ # Configuration files for the application
│ ├── config.php # Base configuration (URLs, constants, session start)
│ └── database.php # PDO database connection setup
│
├── controllers/ # Business logic handlers (MVC Controllers)
│ ├── AuthController.php # Handles user authentication (login, register, logout)
│ ├── CartController.php # Manages shopping cart operations
│ ├── FishController.php # Handles fish product CRUD operations
│ ├── OrderController.php # Manages order processing and analytics
│ └── UserController.php # Handles user profile and staff management
│
├── models/ # Database models (MVC Models)
│ ├── Cart.php # Cart data model and operations
│ ├── Fish.php # Fish product data model
│ ├── Order.php # Order data model and operations
│ └── User.php # User data model and authentication
│
├── handlers/ # Form submission and action handlers
│ ├── auth_handler.php # Processes authentication requests
│ └── cart_handler.php # Processes cart add/update/remove actions
│
├── public/ # Publicly accessible assets (CSS, JS, Images)
│ ├── css/
│ │ └── style.css # Custom styles (if any beyond Tailwind)
│ ├── js/
│ │ └── script.js # Custom JavaScript functionality
│ └── images/
│ └── default_fish.png # Default product image
│
├── uploads/ # Directory for uploaded product images
│ └── tilapia.jpg # Sample product images
│
├── views/ # Presentation layer (MVC Views)
│ ├── customer/ # Customer-facing pages
│ │ ├── browse_fish.php # Fish product catalog
│ │ ├── cart.php # Shopping cart page
│ │ ├── checkout.php # Checkout and payment page
│ │ ├── my_orders.php # Customer order history
│ │ └── profile.php # Customer profile management
│ │
│ ├── staff/ # Staff/Admin dashboard pages
│ │ ├── dashboard.php # Main dashboard with analytics
│ │ ├── fish_list.php # Manage fish products
│ │ ├── add_fish.php # Add new fish product form
│ │ ├── edit_fish.php # Edit existing fish product
│ │ ├── orders.php # Order management
│ │ ├── users_list.php # Staff user management
│ │ └── add_user.php # Add new staff user
│ │
│ ├── auth/ # Authentication pages
│ │ ├── login.php # User login form
│ │ └── register.php # User registration form
│ │
│ └── includes/ # Reusable components
│ ├── header.php # Common header
│ ├── footer.php # Common footer
│ ├── navbar.php # Navigation bar
│ └── staff_sidebar.php # Staff dashboard sidebar
│
├── index.php # Landing page / redirect to login
└── README.md # This file
🚀 How to Check the Project in Browser
Prerequisites
XAMPP/WAMP/MAMP installed

PHP 7.4 or higher

MySQL 5.7 or higher

Web browser (Chrome, Firefox, Safari, Edge)

Step-by-Step Setup Instructions

1. Install and Start XAMPP
   bash

# Download XAMPP from https://www.apachefriends.org/

# Install and start Apache and MySQL services

2. Place Project in htdocs
   bash

# Windows

C:\xampp\htdocs\lake-victoria-tilapia-depot\

# macOS

/Applications/XAMPP/htdocs/lake-victoria-tilapia-depot/

# Linux

/opt/lampp/htdocs/lake-victoria-tilapia-depot/ 3. Create Database
sql
-- Open phpMyAdmin (http://localhost/phpmyadmin)
-- Create a new database named: lake_victoria_tilapia_depot
-- Import the SQL schema from the setup instructions 4. Configure Database Connection
Edit config/config.php:

php
// Update these values according to your setup
define('DB_HOST', 'localhost');
define('DB_NAME', 'lake_victoria_tilapia_depot');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty 5. Set Up File Permissions
bash

# Make sure uploads directory is writable

chmod 755 uploads/ 6. Access the Application
Open your web browser and navigate to:

text
http://localhost/lake-victoria-tilapia-depot/
The application should automatically redirect you to the login page.

👥 Demo Accounts for Testing
Admin Account
Username: admin

Password: password

Access: Full system access

Staff Account
Username: staff1

Password: password

Access: Manage products and orders

Customer Account
Username: customer1

Password: password

Access: Browse and purchase fish

🌐 Application URLs
Customer Pages
Login: http://localhost/lake-victoria-tilapia-depot/views/auth/login.php

Browse Fish: http://localhost/lake-victoria-tilapia-depot/views/customer/browse_fish.php

Shopping Cart: http://localhost/lake-victoria-tilapia-depot/views/customer/cart.php

My Orders: http://localhost/lake-victoria-tilapia-depot/views/customer/my_orders.php

Profile: http://localhost/lake-victoria-tilapia-depot/views/customer/profile.php

Staff/Admin Pages
Dashboard: http://localhost/lake-victoria-tilapia-depot/views/staff/dashboard.php

Fish Management: http://localhost/lake-victoria-tilapia-depot/views/staff/fish_list.php

Order Management: http://localhost/lake-victoria-tilapia-depot/views/staff/orders.php

User Management: http://localhost/lake-victoria-tilapia-depot/views/staff/users_list.php

🛠️ Troubleshooting
Common Issues and Solutions

1. Page Not Found (404)
   Ensure project is in htdocs folder

Check if Apache is running in XAMPP

Verify file paths in URLs

2. Database Connection Error
   Check MySQL is running in XAMPP

Verify database credentials in config/config.php

Ensure database exists and tables are created

3. Image Upload Issues
   Check uploads/ directory permissions

Verify uploads/ directory exists

Check PHP file upload settings in php.ini

4. Session Errors
   Check session_start() is called

Verify no output before session start

Check browser cookies are enabled

5. CSS/JS Not Loading
   Check Tailwind CDN is accessible

Verify file paths in href and src attributes

Check browser console for 404 errors

XAMPP Specific Settings
Edit php.ini (in XAMPP installation):

ini
; Increase file upload size
upload_max_filesize = 10M
post_max_size = 10M

; Enable error reporting for development
display_errors = On
error_reporting = E_ALL
📱 Features to Test
Customer Side
User registration and login

Browse fish catalog with search

Add items to cart

Update cart quantities

Checkout process

Order history viewing

Profile management

Staff Side
Dashboard with analytics

Add/edit/delete fish products

Manage orders (update status)

View sales reports

Low stock alerts

User management (admin only)

🔧 Development Notes
Built with PHP 7.4+ and MySQL

Uses PDO for secure database operations

MVC architecture pattern

Responsive design with Tailwind CSS

MPESA payment integration ready

Secure password hashing

SQL injection prevention

File upload with validation

📞 Support
If you encounter any issues:

Check the troubleshooting section above

Verify all prerequisites are met

Check XAMPP error logs

Ensure database is properly imported

The application should be fully functional once you complete the setup steps above! 🎉
