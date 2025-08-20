# Slot Availability Management System

## Overview

The NBA Badminton Academy booking system now includes an automated slot availability management system that updates court availability when admin approves or rejects bookings. This ensures that the slot availability shown on the main page (`index.php`) accurately reflects the current booking status.

## How It Works

### 1. Booking Types and Availability

The system handles two types of bookings differently:

#### Daily Bookings
- **Availability Calculation**: Calculated dynamically based on approved daily bookings for specific dates
- **Database Impact**: No changes to the `slots` table
- **Real-time**: Availability is calculated on-the-fly when checking slot availability

#### Monthly Bookings
- **Availability Calculation**: When approved, they reduce the `available_members` in the `slots` table for the entire duration
- **Database Impact**: Updates the `slots` table's `available_members` field
- **Persistent**: Changes remain until the booking is rejected or expires

### 2. Admin Approval Process

When an admin approves or rejects a booking, the system automatically:

1. **Updates the booking status** in the `bookings` table
2. **Updates slot availability** based on the action:
   - **Approval**: Reduces available members for monthly bookings
   - **Rejection**: Restores available members for previously approved monthly bookings
3. **Logs the activity** in the `admin_logs` table
4. **Updates monthly_bookings table** if applicable

### 3. Slot Availability Calculation

The final availability shown to users is calculated as:

```
Final Available Members = Slot Available Members - Daily Booked Members
```

Where:
- **Slot Available Members**: From `slots` table (affected by monthly bookings)
- **Daily Booked Members**: Sum of approved daily bookings for the specific date

## Database Structure

### Key Tables

1. **`slots`** - Base slot information
   - `court_number` - Court number (1-8)
   - `time_slot` - Time slot (06:00, 07:00, etc.)
   - `max_members` - Maximum capacity (6)
   - `available_members` - Available capacity (updated by monthly bookings)

2. **`bookings`** - All booking records
   - `status` - pending/approved/rejected
   - `booking_type` - daily/monthly
   - `members_count` - Number of members
   - `court_slot` - Court number
   - `time_slot` - Time slot

3. **`admin_logs`** - Admin activity tracking
   - Records all approval/rejection actions
   - Tracks changes in booking status

## Files Modified/Created

### Core Files
- `admin/dashboard.php` - Added slot availability update functions
- `check_slot_availability.php` - Updated to consider slots table for monthly bookings
- `admin/manage_slots.php` - New admin utility for slot management

### Test Files
- `test_slot_availability.php` - Test script to verify functionality

## Admin Functions

### 1. Automatic Updates
When approving/rejecting bookings, the system automatically:
- Updates slot availability for monthly bookings
- Logs all activities
- Maintains data consistency

### 2. Manual Management
Admins can access the "Manage Slots" page to:
- **Recalculate Availability**: Recalculate all slot availability based on approved monthly bookings
- **Reset All Slots**: Reset all slots to maximum capacity (use with caution)
- **View Statistics**: See current slot status and booking counts

## Usage Instructions

### For Admins

1. **Access Slot Management**:
   - Login to admin dashboard
   - Click "Manage Slots" in the header
   - Use the tools to manage slot availability

2. **Approving Bookings**:
   - Go to the admin dashboard
   - Review pending bookings
   - Click "Approve" or "Reject"
   - System automatically updates slot availability

3. **Recalculating Availability**:
   - Use "Recalculate Availability" if you suspect data inconsistency
   - This ensures the slots table matches approved monthly bookings

### For Users

1. **Viewing Availability**:
   - Visit the main page (`index.php`)
   - Select a date and time
   - View real-time availability for all courts

2. **Booking Process**:
   - Select available court and time
   - Complete booking form
   - Wait for admin approval
   - Slot availability updates automatically upon approval

## Technical Details

### Functions Added

#### `updateSlotAvailability($pdo, $booking)`
- Updates slot availability when a monthly booking is approved
- Reduces `available_members` in the `slots` table
- Only affects monthly bookings

#### `restoreSlotAvailability($pdo, $booking)`
- Restores slot availability when a monthly booking is rejected
- Increases `available_members` in the `slots` table
- Only affects monthly bookings

#### `recalculateSlotAvailability($pdo)`
- Recalculates all slot availability based on approved monthly bookings
- Resets all slots to maximum capacity first
- Then applies all approved monthly bookings

### SQL Queries

#### Slot Availability Check
```sql
SELECT 
    s.court_number,
    s.max_members,
    s.available_members as slot_available_members,
    COALESCE(SUM(
        CASE 
            WHEN b.booking_type = 'daily' AND b.status = 'approved' THEN b.members_count
            ELSE 0 
        END
    ), 0) as daily_booked_members,
    COALESCE(SUM(
        CASE 
            WHEN b.booking_type = 'monthly' AND b.status = 'approved' THEN b.members_count
            ELSE 0 
        END
    ), 0) as monthly_booked_members
FROM slots s
LEFT JOIN bookings b ON s.court_number = b.court_slot 
    AND s.time_slot = b.time_slot 
    AND (
        (b.booking_type = 'daily' AND b.start_date = ?) OR
        (b.booking_type = 'monthly' AND 
         DATE_FORMAT(?, '%Y-%m') >= b.start_month AND 
         DATE_FORMAT(?, '%Y-%m') < DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(b.start_month, '-01'), '%Y-%m-%d'), INTERVAL b.duration MONTH), '%Y-%m'))
    )
WHERE s.time_slot = ? AND s.status = 'active'
GROUP BY s.court_number, s.max_members, s.available_members
```

## Testing

### Test Script
Run `test_slot_availability.php` to verify:
- Current slot availability
- Approved monthly bookings count
- Pending bookings count
- Slot availability calculation
- Recent admin activity

### Manual Testing
1. Create a monthly booking
2. Approve it in admin dashboard
3. Check slot availability on main page
4. Verify the court shows reduced availability

## Troubleshooting

### Common Issues

1. **Slot availability not updating**:
   - Check if the booking is monthly type
   - Verify admin approval was successful
   - Use "Recalculate Availability" in admin panel

2. **Inconsistent availability**:
   - Run the recalculation tool
   - Check for any failed database transactions
   - Review admin logs for errors

3. **Daily bookings not affecting availability**:
   - Daily bookings are calculated dynamically
   - Check if the booking is approved
   - Verify the date and time selection

### Error Logging
All errors are logged to the PHP error log. Check for:
- Database connection issues
- Failed slot updates
- Admin activity logging errors

## Security Considerations

1. **Admin Authentication**: All slot management requires admin login
2. **Input Validation**: All booking data is validated before processing
3. **SQL Injection Protection**: All queries use prepared statements
4. **Activity Logging**: All admin actions are logged for audit purposes

## Future Enhancements

1. **Automatic Expiry**: Handle expired monthly bookings automatically
2. **Conflict Detection**: Prevent overbooking during approval process
3. **Email Notifications**: Notify users when their booking is approved/rejected
4. **Advanced Reporting**: Detailed availability reports for admins
