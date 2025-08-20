<?php
require_once 'admin/config.php';

echo "<h1>Admin Dashboard Test</h1>\n";

// Test 1: Check if admin_logs table exists
echo "<h2>Test 1: Admin Logs Table</h2>\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_logs'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Admin logs table exists<br>\n";
    } else {
        echo "✗ Admin logs table does not exist<br>\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking table: " . $e->getMessage() . "<br>\n";
}

// Test 2: Check booking statistics
echo "<h2>Test 2: Booking Statistics</h2>\n";
try {
    $totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pendingCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $approvedCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'approved'")->fetchColumn();
    $rejectedCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'rejected'")->fetchColumn();
    $dailyCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_type = 'daily'")->fetchColumn();
    $monthlyCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_type = 'monthly'")->fetchColumn();
    
    echo "Total Bookings: {$totalBookings}<br>\n";
    echo "Pending: {$pendingCount}<br>\n";
    echo "Approved: {$approvedCount}<br>\n";
    echo "Rejected: {$rejectedCount}<br>\n";
    echo "Daily: {$dailyCount}<br>\n";
    echo "Monthly: {$monthlyCount}<br>\n";
    
} catch (Exception $e) {
    echo "✗ Error getting statistics: " . $e->getMessage() . "<br>\n";
}

// Test 3: Check bookings by status
echo "<h2>Test 3: Bookings by Status</h2>\n";
try {
    $pendingBookings = $pdo->query("SELECT * FROM bookings WHERE status = 'pending' ORDER BY created_at DESC LIMIT 3")->fetchAll();
    $approvedBookings = $pdo->query("SELECT * FROM bookings WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3")->fetchAll();
    $rejectedBookings = $pdo->query("SELECT * FROM bookings WHERE status = 'rejected' ORDER BY created_at DESC LIMIT 3")->fetchAll();
    
    echo "Pending Bookings: " . count($pendingBookings) . "<br>\n";
    echo "Approved Bookings: " . count($approvedBookings) . "<br>\n";
    echo "Rejected Bookings: " . count($rejectedBookings) . "<br>\n";
    
    if (count($pendingBookings) > 0) {
        echo "<h3>Recent Pending Bookings:</h3>\n";
        foreach ($pendingBookings as $booking) {
            echo "- ID: {$booking['id']}, Type: {$booking['booking_type']}, Court: {$booking['court_slot']}, Status: {$booking['status']}<br>\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error getting bookings: " . $e->getMessage() . "<br>\n";
}

// Test 4: Check admin logs
echo "<h2>Test 4: Admin Logs</h2>\n";
try {
    $recentLogs = $pdo->query("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
    echo "Recent Admin Logs: " . count($recentLogs) . "<br>\n";
    
    if (count($recentLogs) > 0) {
        echo "<h3>Recent Activity:</h3>\n";
        foreach ($recentLogs as $log) {
            echo "- Action: {$log['action']}, Admin: {$log['admin_username']}, Details: {$log['details']}<br>\n";
        }
    } else {
        echo "No admin logs found yet.<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error getting admin logs: " . $e->getMessage() . "<br>\n";
}

// Test 5: Test admin activity logging function
echo "<h2>Test 5: Admin Activity Logging</h2>\n";
try {
    // Function to log admin activity (copied from dashboard)
    function logAdminActivity($pdo, $adminId, $adminUsername, $action, $bookingId = null, $bookingType = null, $oldStatus = null, $newStatus = null, $details = null) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO admin_logs (admin_id, admin_username, action, booking_id, booking_type, old_status, new_status, details, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->execute([$adminId, $adminUsername, $action, $bookingId, $bookingType, $oldStatus, $newStatus, $details, $ipAddress]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to log admin activity: " . $e->getMessage());
            return false;
        }
    }
    
    // Test logging a sample activity
    $result = logAdminActivity(
        $pdo, 
        1, 
        'test_admin', 
        'test_action', 
        1, 
        'daily', 
        'pending', 
        'approved', 
        'Test activity log entry'
    );
    
    if ($result) {
        echo "✓ Admin activity logging function working<br>\n";
    } else {
        echo "✗ Admin activity logging function failed<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error testing admin logging: " . $e->getMessage() . "<br>\n";
}

echo "<h2>Test Complete</h2>\n";
echo "<p>The admin dashboard should now have:</p>\n";
echo "<ul>\n";
echo "<li>Separate tabs for Pending, Approved, and Rejected bookings</li>\n";
echo "<li>Statistics cards showing counts for each category</li>\n";
echo "<li>Admin activity logs showing recent actions</li>\n";
echo "<li>Proper logging when bookings are approved/rejected</li>\n";
echo "</ul>\n";
?>
