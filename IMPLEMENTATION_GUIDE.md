# Lake Victoria Tilapia Depot - Updates & Fixes Implementation Guide

## 🚀 IMPORTANT: Run These Steps in Order

### Step 1: Database Updates

Run the following SQL script in phpMyAdmin to update your database schema:

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `lake_victoria_tilapia_depot` database
3. Click on "SQL" tab
4. Copy and paste the contents of `database/update_schema.sql`
5. Click "Go" to execute

### Step 2: Install PHPMailer (Optional - for email notifications)

Open your terminal in the project directory and run:

```bash
composer require phpmailer/phpmailer
```

If you don't have composer, download it from: https://getcomposer.org/

**Note:** Email notifications will fall back to PHP's mail() function if PHPMailer is not installed.

### Step 3: Configure Notification Settings

Edit `config/config.php` and update the following:

**MPESA Configuration:**

- `MPESA_CONSUMER_KEY` - Your Safaricom Daraja API consumer key
- `MPESA_CONSUMER_SECRET` - Your Safaricom Daraja API consumer secret
- `MPESA_SHORTCODE` - Your business shortcode
- `MPESA_PASSKEY` - Your Lipa Na MPESA passkey
- `MPESA_ENVIRONMENT` - Use 'sandbox' for testing, 'production' for live

**Email Configuration:**

- `MAIL_HOST` - Your SMTP server (e.g., smtp.gmail.com)
- `MAIL_USERNAME` - Your email address
- `MAIL_PASSWORD` - Your email password or app password
- `MAIL_FROM_EMAIL` - Sender email address
- `MAIL_FROM_NAME` - Sender name

**SMS Configuration (Africa's Talking):**

- `SMS_API_KEY` - Your Africa's Talking API key
- `SMS_USERNAME` - Your Africa's Talking username
- `SMS_SHORTCODE` - Your SMS sender ID
- `SMS_ENVIRONMENT` - Use 'sandbox' for testing

**Admin Settings:**

- `ADMIN_EMAIL` - Admin email for notifications
- `ADMIN_PHONE` - Admin phone number for SMS notifications

### Step 4: Test the New Features

## ✅ New Features Implemented

### 1. **Staff Management (Admin Only)**

- **Access:** Login as admin → Click "User Management" in sidebar
- **Location:** `views/staff/users_list.php`
- **Features:**
  - View all users with their roles
  - Add new staff/admin users
  - Edit user information
  - Delete users (except yourself)
  - Search users by name, email, or username
  - User statistics dashboard

### 2. **Profile Page (All Users)**

- **Access:** All users can access their profile
  - **Customers:** Navigate to "Profile" from navbar
  - **Staff/Admin:** Navigate to profile from staff area
- **Location:** `views/customer/profile.php` (works for all roles)
- **Features:**
  - View and edit personal information
  - Change password
  - Account preferences
  - Role-specific layout (staff see sidebar, customers see navbar)

### 3. **MPESA STK Push Integration**

- **Controller:** `controllers/MpesaController.php`
- **Callback:** `callback/mpesa_callback.php`
- **Features:**
  - Initiates STK Push to customer's phone
  - Handles payment callbacks
  - Updates order payment status
  - Stores MPESA receipt number

### 4. **Email & SMS Notifications**

- **Service:** `controllers/NotificationService.php`
- **Features:**
  - Order confirmation emails to customers
  - Order cancellation notifications
  - New order alerts to admin
  - SMS notifications for orders
  - Beautiful HTML email templates

### 5. **Quantity in Kilograms (KG)**

- Cart and order items now support specifying quantity in KG
- Database fields added: `quantity_kg` in `cart` and `order_items` tables
- Price calculation: Unit Price × Quantity (KG)

### 6. **Delete Cancelled Orders**

- Customers can delete their own orders if status is "Cancelled"
- Available in "My Orders" page
- Confirmation dialog before deletion

### 7. **Fixed Bugs**

- ✅ Order status update now persists correctly
- ✅ Cart delete button works properly
- ✅ Shopping cart totals update automatically
- ✅ Staff management accessible from sidebar

## 📂 Files Added/Modified

### New Files Created:

1. `database/update_schema.sql` - Database schema updates
2. `controllers/NotificationService.php` - Email/SMS notification system
3. `views/staff/users_list.php` - Staff management page
4. `views/staff/add_user.php` - Add new user page
5. `views/staff/edit_user.php` - Edit user page

### Files Modified:

1. `config/config.php` - Added notification and MPESA settings
2. `controllers/UserController.php` - Added full CRUD methods
3. `models/User.php` - Enhanced with staff management functions
4. `views/customer/profile.php` - Updated to work for all user roles
5. `handlers/cart_handler.php` - Fixed delete functionality
6. `controllers/MpesaController.php` - Fixed STK push integration

## 🔧 Configuration Checklist

- [ ] Run database update script (`update_schema.sql`)
- [ ] Install PHPMailer (optional)
- [ ] Configure MPESA credentials
- [ ] Configure email settings
- [ ] Configure SMS settings
- [ ] Test staff management as admin
- [ ] Test profile page as all user types
- [ ] Test MPESA payment (sandbox mode first)
- [ ] Test email notifications
- [ ] Test SMS notifications
- [ ] Test cart operations
- [ ] Test order management

## 🧪 Testing Accounts

**Admin:**

- Username: admin
- Password: password
- Can access: All features including user management

**Staff:**

- Username: staff1
- Password: password
- Can access: Fish and order management (not user management)

**Customer:**

- Username: customer1
- Password: password
- Can access: Browse fish, cart, orders, profile

## 📝 Important Notes

1. **MPESA Sandbox Testing:**

   - Use test credentials from Safaricom Daraja Portal
   - Use test phone number: 254708374149
   - Test PIN: Usually provided in sandbox documentation

2. **Email Testing:**

   - For Gmail, use "App Passwords" instead of regular password
   - Enable "Less secure app access" or use OAuth2

3. **SMS Testing:**

   - Use Africa's Talking sandbox for testing
   - Sandbox SMS will only be sent to your registered test numbers

4. **File Permissions:**
   - Ensure `uploads/` directory is writable (chmod 755)
   - Check PHP error logs if issues occur

## 🐛 Troubleshooting

**Issue: Staff Management page shows 404**

- Solution: Clear browser cache and check file exists at `views/staff/users_list.php`

**Issue: Email not sending**

- Check SMTP credentials in `config/config.php`
- Verify PHPMailer is installed or use fallback mail()
- Check server error logs

**Issue: MPESA payment fails**

- Verify all MPESA credentials are correct
- Check you're using correct environment (sandbox/production)
- Review error logs for API responses

**Issue: Cart delete not working**

- Clear browser cache
- Check browser console for JavaScript errors
- Verify cart_handler.php permissions

## 📞 Support

If you encounter any issues:

1. Check PHP error logs
2. Check browser console for JavaScript errors
3. Verify database connection
4. Ensure all required fields in config.php are filled

---

**All features have been implemented as requested. Please run the database updates and configure the credentials before testing.**
