# NBA Badminton Academy - Admin Panel

This is a complete admin panel system for managing badminton court bookings at the Nellai Badminton Academy.

## Features

### Customer Booking System
- **Daily Booking Form**: Book courts for specific dates at ₹500/day
- **Monthly Booking Form**: Book courts for extended periods at ₹12,000/month
- **Plan Selection Popup**: Choose between daily and monthly booking plans
- **Court Selection**: 8 courts available with real-time slot availability
- **Payment Methods**: Support for both online and offline payments
- **File Upload**: Payment screenshot upload for online payments

### Admin Panel
- **Secure Login**: Admin authentication system
- **Dashboard**: Overview of all bookings with statistics
- **Booking Management**: Approve, reject, or view pending bookings
- **Real-time Updates**: Instant status updates for bookings
- **Responsive Design**: Works on all devices

## Setup Instructions

### 1. Database Setup
1. Create a MySQL database named `nba_bookings`
2. The system will automatically create required tables on first run
3. Default admin credentials will be created automatically

### 2. File Structure
```
NBA/
├── admin/
│   ├── config.php          # Database configuration
│   ├── login.php           # Admin login page
│   ├── dashboard.php       # Admin dashboard
│   ├── logout.php          # Logout functionality
│   └── index.php           # Admin redirect
├── uploads/                 # Payment screenshot uploads
├── process_booking.php      # Booking form processor
├── booking.php             # Main booking page
├── index.php               # Home page
└── style.css               # Main stylesheet
```

### 3. Default Admin Access
- **Username**: admin
- **Password**: admin123
- **URL**: `http://yoursite.com/admin/`

### 4. Configuration
Edit `admin/config.php` to update database credentials:
```php
$host = 'localhost';
$dbname = 'nba_bookings';
$username = 'root';
$password = '';
```

## Usage

### For Customers
1. Visit the booking page
2. Choose between daily or monthly booking
3. Fill in personal information
4. Select preferred court and date/month
5. Choose payment method
6. Upload payment screenshot (if online payment)
7. Submit booking

### For Admins
1. Login to admin panel
2. View all bookings in the dashboard
3. Approve or reject pending bookings
4. Monitor booking statistics
5. Manage court availability

## Security Features

- **Session Management**: Secure admin sessions
- **SQL Injection Protection**: Prepared statements
- **File Upload Security**: File type and size validation
- **Password Hashing**: Secure password storage using bcrypt

## Technical Details

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Icons**: Font Awesome 5.15.4
- **Responsive**: Mobile-first design approach

## Support

For technical support or questions, please contact the development team.

## License

This project is proprietary software developed for Nellai Badminton Academy.
