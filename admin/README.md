# NBA Badminton Academy - Admin System

## Overview
This admin system allows you to manage badminton court bookings for the Nellai Badminton Academy.

## Default Admin Access
- **Username:** `admin`
- **Password:** `admin123`

## Admin Features

### 1. Dashboard (`dashboard.php`)
- View booking statistics (total, pending, approved, rejected)
- Manage all bookings (approve/reject)
- View booking details including payment screenshots

### 2. Create Admin (`create_admin.php`)
- Create new admin users
- View existing admin users
- Secure password hashing

### 3. Reset Password (`reset_admin_password.php`)
- Reset passwords for existing admin users
- Useful if admin forgets their password

### 4. Logout (`logout.php`)
- Secure session termination

## How to Use

### First Time Setup
1. Import the `nba_bookings.sql` file into your MySQL database
2. Access `admin/login.php` with default credentials
3. Change the default password immediately

### Creating New Admins
1. Login to the admin system
2. Go to Dashboard → "Create Admin" button
3. Fill in the form:
   - Username (unique)
   - Email (unique)
   - Password (minimum 6 characters)
   - Confirm password
4. Click "Create Admin"

### Managing Bookings
1. Login to admin system
2. View all bookings in the dashboard
3. For each booking:
   - **Approve:** Click "Approve" button
   - **Reject:** Click "Reject" button
4. View payment screenshots for online payments

### Resetting Admin Passwords
1. Access `admin/reset_admin_password.php`
2. Select the admin user from dropdown
3. Enter new password and confirm
4. Click "Update Password"

## Security Features
- Session-based authentication
- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- File upload validation for payment screenshots

## File Structure
```
admin/
├── config.php              # Database connection
├── login.php               # Admin login
├── dashboard.php           # Main admin dashboard
├── create_admin.php        # Create new admin users
├── reset_admin_password.php # Reset admin passwords
├── logout.php              # Logout functionality
├── index.php               # Redirect to login
└── README.md               # This file
```

## Database Tables

### `admins` Table
- `id` - Auto-increment primary key
- `username` - Unique admin username
- `email` - Admin email address
- `password` - Hashed password
- `created_at` - Account creation timestamp

### `bookings` Table
- `id` - Auto-increment primary key
- `name` - Customer name
- `email` - Customer email
- `phone` - Customer phone
- `aadhaar` - Customer Aadhaar number
- `booking_type` - 'daily' or 'monthly'
- `court_slot` - Selected court number
- `start_date` - For daily bookings
- `start_month` - For monthly bookings
- `duration` - For monthly bookings (months)
- `payment_method` - 'online' or 'offline'
- `payment_screenshot` - File path for online payments
- `status` - 'pending', 'approved', or 'rejected'
- `created_at` - Booking creation timestamp
- `updated_at` - Last update timestamp

## Troubleshooting

### Database Connection Issues
- Check database credentials in `config.php`
- Ensure MySQL service is running
- Verify database `nba_bookings` exists

### Login Issues
- Verify username and password
- Check if admin user exists in database
- Clear browser cookies if needed

### File Upload Issues
- Ensure `uploads/` directory exists and is writable
- Check file size limits in PHP configuration
- Verify file type restrictions

## Support
For technical support, check:
1. PHP error logs
2. MySQL error logs
3. File permissions
4. Database connectivity

## Security Recommendations
1. Change default admin password immediately
2. Use strong passwords for all admin accounts
3. Regularly backup the database
4. Keep PHP and MySQL updated
5. Monitor access logs
6. Use HTTPS in production
