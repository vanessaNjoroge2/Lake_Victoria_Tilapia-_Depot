# M-Pesa Payment Configuration Guide

## ⚠️ URGENT SECURITY NOTICE

Your M-Pesa credentials and email password are currently exposed in `config.php`.

**IMMEDIATELY:**

1. Regenerate your M-Pesa credentials at https://developer.safaricom.co.ke/
2. Change your Gmail app password
3. Update the values using environment variables (recommended) or in config.php

---

## Quick Fix Steps

### Option 1: Using Environment Variables (RECOMMENDED - SECURE)

1. Create or edit your `.env` file in the project root:

```env
MPESA_CONSUMER_KEY=your_actual_consumer_key
MPESA_CONSUMER_SECRET=your_actual_consumer_secret
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your_lipa_na_mpesa_passkey
MPESA_ENVIRONMENT=sandbox
```

2. The code will automatically use these values.

### Option 2: Direct Configuration (LESS SECURE)

Edit `config/config.php` and update:

```php
define('MPESA_CONSUMER_KEY', 'your_actual_key');
define('MPESA_CONSUMER_SECRET', 'your_actual_secret');
define('MPESA_SHORTCODE', '174379');
define('MPESA_PASSKEY', 'your_lipa_na_mpesa_passkey');
define('MPESA_ENVIRONMENT', 'sandbox'); // or 'production'
```

---

## Getting Your M-Pesa Credentials

### For Sandbox (Testing):

1. Go to https://developer.safaricom.co.ke/
2. Sign in or create account
3. Create a new application
4. Copy the **Consumer Key** and **Consumer Secret**
5. Get the **Lipa Na M-Pesa Passkey** from your M-Pesa settings

### For Production:

- Use your actual M-Pesa business account credentials
- Change `MPESA_ENVIRONMENT` to `'production'`
- Get production credentials from Safaricom

---

## Default Sandbox Passkey

For testing purposes, if you don't have a passkey yet, the sandbox default is:

```
bfb279f9aa9bdbcf158e97dd1a503b6e
```

---

## Testing the Configuration

### Test Case 1: Invalid Phone Number

- Phone: `0123456789` (invalid)
- Expected: "Invalid phone number format"

### Test Case 2: Invalid Amount

- Amount: `0` or `200000`
- Expected: "Invalid amount" error

### Test Case 3: Successful Payment

- Phone: `0712345678` or `254712345678`
- Amount: `100` (KSH)
- Expected: STK push prompt on phone

---

## Common Error Messages & Solutions

### "Failed to get access token"

**Causes:**

- Consumer Key/Secret not configured
- Network connectivity issue
- Sandbox/Production environment mismatch

**Solution:**

1. Verify credentials are set in config.php or .env
2. Check internet connection
3. Verify `MPESA_ENVIRONMENT` setting matches credentials

### "Invalid phone number format"

**Causes:**

- Missing country code
- Invalid phone digits
- Special characters

**Solution:**

- Use format: `0712345678` (local) or `254712345678` (international)
- Remove hyphens, spaces, or +

### "Invalid amount"

**Causes:**

- Amount less than 1 KSH
- Amount greater than 150,000 KSH

**Solution:**

- Ensure amount is between 1-150,000 KSH

### "Payment initiation failed (Generic)"

**Causes:**

- Passkey not configured
- All of the above

**Solution:**

1. Check error logs in `php_error_log` or `htdocs/error.log`
2. Ensure all M-Pesa credentials are properly configured
3. Verify phone number and amount are valid

---

## How to Check Error Logs

Error logs are written to PHP error log. Locations:

- **XAMPP**: `C:\xampp3\apache\logs\error.log`
- **Firefox/Chrome**: Developer Console (F12) → Network tab
- **PHP Direct**: Check `php_error_log` in error_reporting settings

Look for lines starting with `MPESA`:

```
[timestamp] MPESA STK Push Response Code: 200
[timestamp] MPESA STK Push Response Body: {...}
```

---

## Troubleshooting Checklist

- [ ] M-Pesa Consumer Key is set (not "your_consumer_key_here")
- [ ] M-Pesa Consumer Secret is set (not "your_consumer_secret_here")
- [ ] M-Pesa Passkey is set (not "your_passkey_here")
- [ ] Phone number format is valid (0712345678 or 254712345678)
- [ ] Amount is between 1-150,000 KSH
- [ ] Environment setting (sandbox/production) matches credentials
- [ ] Internet connection is working
- [ ] PHP cURL extension is enabled

---

## Support Resources

- **M-Pesa API Docs**: https://developer.safaricom.co.ke/docs
- **Common Issues**: https://developer.safaricom.co.ke/docs#troubleshooting
- **Contact Support**: support@safaricom.co.ke
