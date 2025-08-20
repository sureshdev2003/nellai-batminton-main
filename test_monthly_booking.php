<?php
require_once 'admin/config.php';

echo "<h1>Monthly Booking System Test</h1>\n";

// Test 1: Check if monthly_bookings table exists
echo "<h2>Test 1: Database Structure</h2>\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'monthly_bookings'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Monthly bookings table exists<br>\n";
    } else {
        echo "✗ Monthly bookings table does not exist<br>\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking table: " . $e->getMessage() . "<br>\n";
}

// Test 2: Check monthly availability API directly
echo "<h2>Test 2: Monthly Availability API</h2>\n";
$testMonth = date('Y-m');
$testDuration = 1;
$testTimeSlot = '18:00';

echo "Testing parameters: start_month={$testMonth}, duration={$testDuration}, time_slot={$testTimeSlot}<br>\n";

// Test the availability logic directly
try {
    // Helper to compute overlap between two month ranges
    $parseMonth = function (string $ym): DateTime {
        return DateTime::createFromFormat('Y-m', $ym) ?: new DateTime('1970-01');
    };

    $addMonths = function (DateTime $dt, int $months): DateTime {
        $copy = (clone $dt);
        $copy->modify("first day of this month");
        $copy->modify("+{$months} months"); // exclusive end
        return $copy;
    };

    $selStart = $parseMonth($testMonth);
    $selEnd = $addMonths($selStart, $testDuration); // exclusive end

    // Get slot availability from the slots table (like daily booking)
    $stmt = $pdo->prepare("
        SELECT 
            s.court_number,
            s.max_members,
            s.available_members,
            COALESCE(SUM(
                CASE 
                    WHEN b.booking_type = 'monthly' THEN b.members_count
                    ELSE 0 
                END
            ), 0) as monthly_booked_members
        FROM slots s
        LEFT JOIN bookings b ON s.court_number = b.court_slot 
            AND s.time_slot = b.time_slot 
            AND b.status IN ('pending', 'approved')
            AND b.booking_type = 'monthly'
            AND (
                DATE_FORMAT(STR_TO_DATE(?, '%Y-%m'), '%Y-%m') >= b.start_month AND 
                DATE_FORMAT(STR_TO_DATE(?, '%Y-%m'), '%Y-%m') < DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(b.start_month, '-01'), '%Y-%m-%d'), INTERVAL b.duration MONTH), '%Y-%m')
            )
        WHERE s.time_slot = ? AND s.status = 'active'
        GROUP BY s.court_number, s.max_members, s.available_members
        ORDER BY s.court_number
    ");
    
    $stmt->execute([$testMonth, $testMonth, $testTimeSlot]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create availability array for all 8 courts
    $availability = [];
    for ($i = 1; $i <= 8; $i++) {
        $availability[$i] = [
            'court' => $i,
            'capacity' => 6, // Default capacity
            'available' => 6, // Default available
            'booked' => 0,
            'status' => 'Available'
        ];
    }
    
    // Update with actual data from database
    foreach ($slots as $slot) {
        $courtNumber = $slot['court_number'];
        $monthlyBookedMembers = (int)$slot['monthly_booked_members'];
        $maxMembers = (int)$slot['max_members'];
        $availableMembers = $maxMembers - $monthlyBookedMembers;
        
        $availability[$courtNumber] = [
            'court' => $courtNumber,
            'capacity' => $maxMembers,
            'available' => $availableMembers,
            'booked' => $monthlyBookedMembers,
            'status' => $availableMembers > 0 ? 'Available' : 'Full'
        ];
    }

    echo "✓ Monthly availability calculation working<br>\n";
    echo "<h3>Availability Summary:</h3>\n";
    foreach ($availability as $court => $info) {
        $status = $info['available'] > 0 ? 'Available' : 'Full';
        echo "Court {$court}: {$info['available']}/{$info['capacity']} members ({$status})<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Monthly availability calculation error: " . $e->getMessage() . "<br>\n";
}

// Test 3: Check slot availability with monthly bookings directly
echo "<h2>Test 3: Slot Availability with Monthly Bookings</h2>\n";
$testDate = date('Y-m-d');
$testTime = '18:00';

echo "Testing parameters: date={$testDate}, time={$testTime}<br>\n";

try {
    // Get slot availability for all courts at the specified time
    // This includes both daily bookings and monthly bookings that overlap with the date
    $stmt = $pdo->prepare("
        SELECT 
            s.court_number,
            s.max_members,
            s.available_members,
            COALESCE(SUM(
                CASE 
                    WHEN b.booking_type = 'daily' THEN b.members_count
                    ELSE 0 
                END
            ), 0) as daily_booked_members,
            COALESCE(SUM(
                CASE 
                    WHEN b.booking_type = 'monthly' THEN b.members_count
                    ELSE 0 
                END
            ), 0) as monthly_booked_members
        FROM slots s
        LEFT JOIN bookings b ON s.court_number = b.court_slot 
            AND s.time_slot = b.time_slot 
            AND b.status IN ('pending', 'approved')
            AND (
                (b.booking_type = 'daily' AND b.start_date = ?) OR
                (b.booking_type = 'monthly' AND 
                 DATE_FORMAT(?, '%Y-%m') >= b.start_month AND 
                 DATE_FORMAT(?, '%Y-%m') < DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(b.start_month, '-01'), '%Y-%m-%d'), INTERVAL b.duration MONTH), '%Y-%m'))
            )
        WHERE s.time_slot = ? AND s.status = 'active'
        GROUP BY s.court_number, s.max_members, s.available_members
        ORDER BY s.court_number
    ");
    
    $stmt->execute([$testDate, $testDate, $testDate, $testTime]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create availability array for all 8 courts
    $availability = [];
    for ($i = 1; $i <= 8; $i++) {
        $availability[$i] = [
            'court' => $i,
            'available' => false,
            'max_members' => 6,
            'available_members' => 0,
            'booked_members' => 0,
            'status' => 'Not Available'
        ];
    }
    
    // Update with actual data from database
    foreach ($slots as $slot) {
        $courtNumber = $slot['court_number'];
        $dailyBookedMembers = (int)$slot['daily_booked_members'];
        $monthlyBookedMembers = (int)$slot['monthly_booked_members'];
        $totalBookedMembers = $dailyBookedMembers + $monthlyBookedMembers;
        $maxMembers = (int)$slot['max_members'];
        $availableMembers = $maxMembers - $totalBookedMembers;
        
        $availability[$courtNumber] = [
            'court' => $courtNumber,
            'available' => $availableMembers > 0,
            'max_members' => $maxMembers,
            'available_members' => $availableMembers,
            'booked_members' => $totalBookedMembers,
            'daily_booked_members' => $dailyBookedMembers,
            'monthly_booked_members' => $monthlyBookedMembers,
            'status' => $availableMembers > 0 ? "Available ($availableMembers members)" : "Full"
        ];
    }
    
    echo "✓ Slot availability calculation working<br>\n";
    echo "<h3>Slot Availability Summary:</h3>\n";
    foreach ($availability as $court => $info) {
        echo "Court {$court}: {$info['status']} (Daily: {$info['daily_booked_members']}, Monthly: {$info['monthly_booked_members']})<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Slot availability calculation error: " . $e->getMessage() . "<br>\n";
}

// Test 4: Check existing monthly bookings
echo "<h2>Test 4: Existing Monthly Bookings</h2>\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE booking_type = 'monthly'");
    $monthlyCount = $stmt->fetchColumn();
    echo "Total monthly bookings: {$monthlyCount}<br>\n";
    
    if ($monthlyCount > 0) {
        $stmt = $pdo->query("SELECT * FROM bookings WHERE booking_type = 'monthly' ORDER BY created_at DESC LIMIT 5");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Recent monthly bookings:<br>\n";
        foreach ($bookings as $booking) {
            echo "- ID: {$booking['id']}, Court: {$booking['court_slot']}, Time: {$booking['time_slot']}, Month: {$booking['start_month']}, Duration: {$booking['duration']}<br>\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking monthly bookings: " . $e->getMessage() . "<br>\n";
}

// Test 5: Check slots table
echo "<h2>Test 5: Slots Table</h2>\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM slots");
    $slotsCount = $stmt->fetchColumn();
    echo "Total entries in slots table: {$slotsCount}<br>\n";
    
    // Check slots for a specific time
    $stmt = $pdo->prepare("SELECT * FROM slots WHERE time_slot = '18:00' ORDER BY court_number LIMIT 8");
    $stmt->execute();
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Slots for 18:00 time:<br>\n";
    foreach ($slots as $slot) {
        echo "- Court {$slot['court_number']}: {$slot['available_members']}/{$slot['max_members']} members<br>\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking slots table: " . $e->getMessage() . "<br>\n";
}

// Test 6: Check monthly_bookings table
echo "<h2>Test 6: Monthly Bookings Table</h2>\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM monthly_bookings");
    $monthlyTableCount = $stmt->fetchColumn();
    echo "Total entries in monthly_bookings table: {$monthlyTableCount}<br>\n";
    
    if ($monthlyTableCount > 0) {
        $stmt = $pdo->query("SELECT * FROM monthly_bookings ORDER BY created_at DESC LIMIT 5");
        $monthlyBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Recent monthly_bookings entries:<br>\n";
        foreach ($monthlyBookings as $booking) {
            echo "- ID: {$booking['id']}, Booking ID: {$booking['booking_id']}, Court: {$booking['court_slot']}, Time: {$booking['time_slot']}, Month: {$booking['start_month']}, Duration: {$booking['duration']}<br>\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking monthly_bookings table: " . $e->getMessage() . "<br>\n";
}

echo "<h2>Test Complete</h2>\n";
echo "<p>If all tests pass, the monthly booking system should be working correctly.</p>\n";
?>
