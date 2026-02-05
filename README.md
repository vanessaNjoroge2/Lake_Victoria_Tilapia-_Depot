# 🐟 Lake Victoria Tilapia Depot

> A modern, full-featured e-commerce web system for fresh tilapia fish sales from Lake Victoria

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.0+-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)

## 📖 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Screenshots](#screenshots)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Demo Accounts](#demo-accounts)
- [Application URLs](#application-urls)
- [Technologies Used](#technologies-used)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

---

## 🌟 Overview

Lake Victoria Tilapia Depot is a comprehensive web-based e-commerce system designed for managing and selling fresh tilapia fish online. The application features a stunning landing page, complete customer shopping experience, staff management dashboard, and administrative controls.

### 🎯 Key Highlights

- ✅ **Modern Landing Page** - Beautiful, responsive design with smooth animations
- ✅ **Customer Portal** - Shopping cart, checkout, and order tracking
- ✅ **M-PESA Integration** - Secure mobile payment processing
- ✅ **Staff Dashboard** - Analytics, inventory, and order management
- ✅ **Admin Controls** - User management and system configuration
- ✅ **Notification System** - Email and SMS alerts
- ✅ **Mobile-First Design** - Fully responsive across all devices
- ✅ **Role-Based Access** - Customer, Staff, and Admin roles

---

## ✨ Features

### 🏠 Landing Page (NEW!)

- **Hero Section** - Eye-catching gradient design with floating fish animations
- **About Us** - Company story, mission, and values with imagery
- **Services Display** - Fresh fish sales, cleaning, deep frying, bulk supply
- **Products Showcase** - Tilapia in small (Ksh 150-250), medium (Ksh 300-500), and large (Ksh 600-900) sizes
- **Contact Form** - Integrated form with WhatsApp quick contact button
- **Smooth Navigation** - Sticky header with smooth scroll between sections
- **Full Responsiveness** - Optimized for desktop, tablet, and mobile devices
- **SEO Optimized** - Meta tags and semantic HTML structure

### 👥 Customer Features

- **User Authentication** - Secure registration and login system
- **Product Catalog** - Browse fish with search and filter options
- **Shopping Cart** - Add, update, and remove items easily
- **Checkout Process** - Streamlined checkout with address and delivery options
- **M-PESA Payment** - STK Push integration for secure payments
- **Order Tracking** - Real-time order status updates
- **Order History** - View past orders and reorder easily
- **Profile Management** - Update personal information and preferences
- **Notifications** - Email and SMS alerts for order updates

### 👨‍💼 Staff Features

- **Analytics Dashboard** - Sales statistics, revenue, and customer metrics
- **Product Management** - Add, edit, delete fish products with images
- **Inventory Control** - Track stock levels and low stock alerts
- **Order Management** - View, update, and process customer orders
- **Order Status Updates** - Change order status (pending, processing, completed, cancelled)
- **Sales Reports** - Daily, weekly, and monthly sales analysis
- **Customer Insights** - View customer statistics and purchase patterns

### 🔐 Admin Features

- **User Management** - Create, edit, delete staff and customer accounts
- **Role Assignment** - Assign roles (admin, staff, customer)
- **System Configuration** - Update settings and preferences
- **Complete Oversight** - Access to all orders and transactions
- **Analytics & Reporting** - Comprehensive business intelligence
- **Access Control** - Manage permissions and security settings

---

## 📸 Screenshots

### Landing Page

- Modern hero section with Lake Victoria background
- Service cards with icons and descriptions
- Product showcase with pricing
- Contact form and business information

### Customer Portal

- Product browsing with images and pricing
- Shopping cart with quantity controls
- Checkout with M-PESA integration
- Order tracking and history

### Staff Dashboard

- Sales analytics and charts
- Product management interface
- Order processing workflow
- Inventory management

---

## 📁 Project Structure

```
lake-victoria-tilapia-depot/
│
├── config/                         # Configuration files
│   ├── config.php                 # Base configuration
│   └── database.php               # Database connection
│
├── controllers/                   # Business logic (MVC)
│   ├── AuthController.php         # Authentication
│   ├── CartController.php         # Shopping cart
│   ├── FishController.php         # Product management
│   ├── MpesaController.php        # Payment processing
│   ├── NotificationService.php    # Email/SMS
│   ├── OrderController.php        # Order processing
│   └── UserController.php         # User management
│
├── models/                        # Data models
│   ├── cart.php                   # Cart model
│   ├── fish.php                   # Fish model
│   ├── order.php                  # Order model
│   └── user.php                   # User model
│
├── handlers/                      # Form handlers
│   ├── auth_handler.php           # Auth processing
│   └── cart_handler.php           # Cart operations
│
├── database/                      # Database schemas
│   ├── schema.sql                 # Initial schema
│   └── update_schema.sql          # Schema updates
│
├── uploads/                       # Product images
│   ├── fresh_tilapia.jpg
│   ├── fresh_tilapia_logo.jpg
│   ├── tilapia_in_water.jpg
│   └── ...
│
├── views/                         # Presentation layer
│   ├── customer/                  # Customer pages
│   │   ├── browse_fish.php
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   ├── my_orders.php
│   │   ├── order_details.php
│   │   ├── order_success.php
│   │   └── profile.php
│   │
│   ├── staff/                     # Staff dashboard
│   │   ├── dashboard.php
│   │   ├── fish_list.php
│   │   ├── add_fish.php
│   │   ├── edit_fish.php
│   │   ├── orders.php
│   │   ├── order_details.php
│   │   ├── users_list.php
│   │   ├── add_user.php
│   │   └── edit_user.php
│   │
│   ├── auth/                      # Authentication
│   │   ├── login.php
│   │   └── register.php
│   │
│   ├── public/                    # Public pages
│   │   ├── about.php
│   │   └── terms.php
│   │
│   └── includes/                  # Reusable components
│       ├── header.php
│       ├── footer.php
│       ├── navbar.php
│       └── staff_sidebar.php
│
├── callback/                      # API callbacks
│   └── mpesa_callback.php
│
├── vendor/                        # Dependencies
│   └── phpmailer/
│
├── index.php                      # Entry point → landing page
├── landing.php                    # Modern landing page ⭐
├── composer.json                  # PHP dependencies
├── LANDING_PAGE_GUIDE.md          # Landing page documentation
└── README.md                      # This file
```

---

## 🚀 Installation

### Prerequisites

Before you begin, ensure you have the following installed:

- **XAMPP/WAMP/MAMP** - Local server environment
- **PHP 7.4+** - Programming language
- **MySQL 5.7+** - Database server
- **Composer** (Optional) - For PHPMailer dependency
- **Modern Web Browser** - Chrome, Firefox, Safari, or Edge

### Step-by-Step Setup

#### 1. Install XAMPP

Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

Start Apache and MySQL services from XAMPP Control Panel.

#### 2. Clone or Download Project

Place the project folder in your htdocs directory:

```bash
# Windows
C:\xampp\htdocs\lake-victoria-tilapia-depot\

# macOS
/Applications/XAMPP/htdocs/lake-victoria-tilapia-depot/

# Linux
/opt/lampp/htdocs/lake-victoria-tilapia-depot/
```

#### 3. Create Database

1. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Create a new database named: `lake_victoria_tilapia_depot`
3. Import the schema:
   - Click on the database
   - Go to "SQL" tab
   - Open `database/schema.sql` file
   - Copy all contents
   - Paste into SQL window
   - Click "Go" to execute

#### 4. Run Database Updates (Important!)

After importing the initial schema, run the updates:

1. Still in phpMyAdmin, with your database selected
2. Go to "SQL" tab
3. Open `database/update_schema.sql` file
4. Copy all contents
5. Paste and execute

This adds important features like:

- Quantity in kg for cart and orders
- Enhanced fish descriptions
- Order status options
- Additional fields

#### 5. Set File Permissions

Ensure the uploads directory is writable:

```bash
# Linux/Mac
chmod 755 uploads/

# Windows
# Right-click uploads folder → Properties → Security → Edit
# Give appropriate write permissions
```

#### 6. Install PHPMailer (Optional)

For email notifications:

```bash
cd lake-victoria-tilapia-depot
composer require phpmailer/phpmailer
```

Or download manually and place in `vendor/phpmailer/` directory.

---

## ⚙️ Configuration

### Database Configuration

Edit `config/config.php` and update database credentials:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lake_victoria_tilapia_depot');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP password is empty
```

### M-PESA Configuration

For payment integration, update these settings in `config/config.php`:

```php
// M-PESA Settings
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_SHORTCODE', 'your_shortcode');
define('MPESA_PASSKEY', 'your_passkey');
define('MPESA_CALLBACK_URL', BASE_URL . '/callback/mpesa_callback.php');
```

Get M-PESA credentials from [Safaricom Daraja Portal](https://developer.safaricom.co.ke/)

### Email Configuration

For email notifications, configure SMTP in `config/config.php`:

```php
// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
define('SMTP_FROM_NAME', 'Lake Victoria Tilapia Depot');
```

### SMS Configuration (Optional)

For SMS notifications via Africa's Talking:

```php
// SMS Settings
define('SMS_API_KEY', 'your_api_key');
define('SMS_USERNAME', 'your_username');
```

### Admin Contact

Update admin contact information:

```php
// Admin Contact
define('ADMIN_EMAIL', 'admin@tilapiadepot.com');
define('ADMIN_PHONE', '+254700000000');
```

---

## 👥 Demo Accounts

Use these accounts for testing different user roles:

### Admin Account

```
Username: admin
Password: password
Access:  Full system access, user management, all features
```

### Staff Account

```
Username: staff1
Password: password
Access:  Product management, order management, dashboard
```

### Customer Account

```
Username: customer1
Password: password
Access:  Browse products, shopping cart, place orders, view history
```

**Note:** Change these passwords in production!

---

## 🌐 Application URLs

### Main Pages

| Page                      | URL                                                                    |
| ------------------------- | ---------------------------------------------------------------------- |
| **Landing Page**          | `http://localhost/lake-victoria-tilapia-depot/`                        |
| **Landing Page (Direct)** | `http://localhost/lake-victoria-tilapia-depot/landing.php`             |
| **Login**                 | `http://localhost/lake-victoria-tilapia-depot/views/auth/login.php`    |
| **Register**              | `http://localhost/lake-victoria-tilapia-depot/views/auth/register.php` |

### Customer Pages

| Page              | URL                                 |
| ----------------- | ----------------------------------- |
| **Browse Fish**   | `/views/customer/browse_fish.php`   |
| **Shopping Cart** | `/views/customer/cart.php`          |
| **Checkout**      | `/views/customer/checkout.php`      |
| **My Orders**     | `/views/customer/my_orders.php`     |
| **Order Details** | `/views/customer/order_details.php` |
| **Profile**       | `/views/customer/profile.php`       |

### Staff/Admin Pages

| Page                 | URL                           |
| -------------------- | ----------------------------- |
| **Dashboard**        | `/views/staff/dashboard.php`  |
| **Fish Management**  | `/views/staff/fish_list.php`  |
| **Add Fish**         | `/views/staff/add_fish.php`   |
| **Order Management** | `/views/staff/orders.php`     |
| **User Management**  | `/views/staff/users_list.php` |

### Public Pages

| Page                   | URL                       |
| ---------------------- | ------------------------- |
| **About Us**           | `/views/public/about.php` |
| **Terms & Conditions** | `/views/public/terms.php` |

---

## 🛠️ Technologies Used

### Backend

- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database management
- **PDO** - Database abstraction layer
- **PHPMailer** - Email sending

### Frontend

- **HTML5** - Markup
- **Tailwind CSS 3.0** - Utility-first CSS framework
- **JavaScript (ES6+)** - Client-side scripting
- **Font Awesome 6.0** - Icons
- **Google Fonts (Poppins)** - Typography

### Architecture

- **MVC Pattern** - Model-View-Controller architecture
- **RESTful API** - For M-PESA integration
- **Session Management** - User authentication
- **AJAX** - Asynchronous requests

### Security

- **Password Hashing** - bcrypt algorithm
- **SQL Injection Prevention** - Prepared statements
- **XSS Protection** - Input sanitization
- **CSRF Protection** - Token-based validation
- **File Upload Validation** - Type and size checks

---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### 1. **Page Not Found (404)**

**Problem:** Cannot access the application

**Solutions:**

- Ensure project is in `htdocs` folder
- Check if Apache is running in XAMPP
- Verify URL: `http://localhost/lake-victoria-tilapia-depot/`
- Check for typos in file paths

#### 2. **Database Connection Error**

**Problem:** "Could not connect to database"

**Solutions:**

- Check MySQL is running in XAMPP
- Verify credentials in `config/config.php`
- Ensure database `lake_victoria_tilapia_depot` exists
- Check if tables are imported from `schema.sql`

#### 3. **Blank/White Page**

**Problem:** Page loads but shows nothing

**Solutions:**

- Enable error reporting in `php.ini`:
  ```ini
  display_errors = On
  error_reporting = E_ALL
  ```
- Check PHP error logs in XAMPP
- Verify all required files exist
- Check file permissions

#### 4. **Image Upload Issues**

**Problem:** Cannot upload product images

**Solutions:**

- Check `uploads/` directory exists
- Verify folder permissions (755 or 777)
- Check PHP upload settings in `php.ini`:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 10M
  ```
- Ensure file types are allowed (jpg, jpeg, png)

#### 5. **Session Errors**

**Problem:** "Headers already sent" or session issues

**Solutions:**

- Check no output before `session_start()`
- Remove any spaces before `<?php`
- Clear browser cookies
- Check session directory permissions

#### 6. **CSS/JavaScript Not Loading**

**Problem:** Page looks unstyled or lacks interactivity

**Solutions:**

- Check internet connection (Tailwind CSS uses CDN)
- Verify CDN URLs are accessible
- Check browser console for 404 errors
- Clear browser cache (Ctrl+Shift+Delete)

#### 7. **M-PESA Payment Not Working**

**Problem:** Payment fails or doesn't initiate

**Solutions:**

- Verify M-PESA credentials in config
- Check sandbox/production mode
- Ensure callback URL is accessible
- Check M-PESA API logs
- Verify phone number format (+254...)

#### 8. **Email Notifications Not Sending**

**Problem:** No email notifications received

**Solutions:**

- Check SMTP configuration
- Enable "Less secure app access" (Gmail)
- Use App Password instead of regular password
- Verify PHPMailer is installed
- Check spam folder

---

## 📱 Testing Checklist

### Desktop Testing

- [ ] Landing page displays correctly
- [ ] Navigation works smoothly
- [ ] All sections scroll properly
- [ ] Images load correctly
- [ ] Login/Register functional
- [ ] Shopping cart works
- [ ] Checkout process completes
- [ ] Staff dashboard accessible
- [ ] Product management works
- [ ] Order management functional

### Mobile Testing

- [ ] Responsive layout on mobile
- [ ] Hamburger menu opens/closes
- [ ] Touch interactions work
- [ ] Images scale properly
- [ ] Forms are usable
- [ ] Buttons are tappable
- [ ] Shopping experience smooth

### Browser Compatibility

- [ ] Google Chrome
- [ ] Mozilla Firefox
- [ ] Safari
- [ ] Microsoft Edge
- [ ] Mobile browsers

---

## 📈 Features to Test

### Customer Side

1. ✅ User registration and login
2. ✅ Browse fish catalog
3. ✅ Search and filter products
4. ✅ Add items to cart
5. ✅ Update cart quantities
6. ✅ Remove items from cart
7. ✅ Checkout process
8. ✅ M-PESA payment (sandbox)
9. ✅ View order history
10. ✅ Track order status
11. ✅ Update profile information

### Staff Side

1. ✅ View dashboard analytics
2. ✅ Add new fish products
3. ✅ Edit existing products
4. ✅ Delete products
5. ✅ Upload product images
6. ✅ View all orders
7. ✅ Update order status
8. ✅ View low stock alerts
9. ✅ Generate sales reports

### Admin Side

1. ✅ Manage users (add/edit/delete)
2. ✅ Assign user roles
3. ✅ View all system data
4. ✅ Access all features
5. ✅ System configuration

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Comment complex logic
- Test before submitting

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 📞 Support

For issues, questions, or suggestions:

- **Email:** admin@tilapiadepot.com
- **Phone:** +254700000000
- **WhatsApp:** Available on landing page
- **GitHub Issues:** [Report a bug](https://github.com/vanessaNjoroge2/Lake-Victoria-Tilapia-Depot/issues)

---

## 🙏 Acknowledgments

- **Tailwind CSS** - For the amazing CSS framework
- **Font Awesome** - For the beautiful icons
- **PHPMailer** - For email functionality
- **Safaricom Daraja** - For M-PESA API
- **XAMPP** - For local development environment

---

## 📝 Documentation

- **Landing Page Guide:** See `LANDING_PAGE_GUIDE.md` for detailed landing page documentation
- **API Documentation:** Coming soon
- **Database Schema:** See `database/schema.sql` for complete structure

---

## 🎯 Roadmap

### Upcoming Features

- [ ] Multiple payment gateways (PayPal, Stripe)
- [ ] Product reviews and ratings
- [ ] Wishlist functionality
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native)
- [ ] Multi-language support
- [ ] Live chat support
- [ ] Loyalty program
- [ ] Referral system
- [ ] API for third-party integrations

---

## 🔒 Security

### Best Practices Implemented

- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ CSRF tokens
- ✅ File upload validation
- ✅ Session security
- ✅ Input sanitization
- ✅ Prepared statements

### Security Recommendations

- Change default passwords immediately
- Use HTTPS in production
- Enable SSL certificate
- Regular security updates
- Backup database regularly
- Monitor error logs
- Implement rate limiting
- Use environment variables for secrets

---

## 💡 Tips for Development

### Local Development

```bash
# Enable error reporting for debugging
display_errors = On
error_reporting = E_ALL
```

### Production Deployment

```bash
# Disable error display
display_errors = Off
log_errors = On
error_log = /path/to/error.log
```

### Database Backup

```bash
# Export database
mysqldump -u root -p lake_victoria_tilapia_depot > backup.sql

# Import database
mysql -u root -p lake_victoria_tilapia_depot < backup.sql
```

---

## 🎉 Success!

Your Lake Victoria Tilapia Depot system is now ready to use!

1. Visit: `http://localhost/lake-victoria-tilapia-depot/`
2. Explore the beautiful landing page
3. Login with demo accounts
4. Test all features
5. Customize as needed

**Happy Coding! 🐟**

---

_Built with ❤️ for Lake Victoria Tilapia Depot_

_Last Updated: January 2026_
