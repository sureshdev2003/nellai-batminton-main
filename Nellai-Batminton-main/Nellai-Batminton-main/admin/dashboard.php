<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Function to update slot availability when booking is approved
function updateSlotAvailability($pdo, $booking) {
    try {
        if ($booking['booking_type'] == 'daily') {
            // For daily bookings, update the specific date's availability
            // This is handled dynamically in check_slot_availability.php
            // No need to update slots table for daily bookings
            return true;
        } else if ($booking['booking_type'] == 'monthly') {
            // For monthly bookings, we need to update the slots table
            // to reflect the reduced availability for the entire duration
            
            // Get the start and end months
            $startMonth = $booking['start_month'];
            $duration = $booking['duration'];
            
            // Calculate end month
            $startDate = DateTime::createFromFormat('Y-m', $startMonth);
            $endDate = clone $startDate;
            $endDate->add(new DateInterval("P{$duration}M"));
            $endDate->sub(new DateInterval('P1D')); // Last day of the last month
            
            // Update slots table for the entire duration
            $stmt = $pdo->prepare("
                UPDATE slots 
                SET available_members = GREATEST(0, available_members - ?) 
                WHERE court_number = ? 
                AND time_slot = ?
            ");
            
            $stmt->execute([
                $booking['members_count'],
                $booking['court_slot'],
                $booking['time_slot']
            ]);
            
            return true;
        }
    } catch (Exception $e) {
        error_log("Failed to update slot availability: " . $e->getMessage());
        return false;
    }
}

// Function to restore slot availability when booking is rejected
function restoreSlotAvailability($pdo, $booking) {
    try {
        if ($booking['booking_type'] == 'daily') {
            // For daily bookings, no need to update slots table
            return true;
        } else if ($booking['booking_type'] == 'monthly') {
            // For monthly bookings, restore the availability
            $stmt = $pdo->prepare("
                UPDATE slots 
                SET available_members = LEAST(max_members, available_members + ?) 
                WHERE court_number = ? 
                AND time_slot = ?
            ");
            
            $stmt->execute([
                $booking['members_count'],
                $booking['court_slot'],
                $booking['time_slot']
            ]);
            
            return true;
        }
    } catch (Exception $e) {
        error_log("Failed to restore slot availability: " . $e->getMessage());
        return false;
    }
}

// Function to log admin activity
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

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    // Get booking details before update
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($booking) {
        $oldStatus = $booking['status'];
        $newStatus = ($action == 'approve') ? 'approved' : 'rejected';
        
        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $booking_id]);
        
        // Update slot availability based on action and previous status
        if ($action == 'approve' && $oldStatus != 'approved') {
            // Approving a booking that wasn't previously approved
            updateSlotAvailability($pdo, $booking);
        } else if ($action == 'reject' && $oldStatus == 'approved') {
            // Rejecting a previously approved booking - restore availability
            restoreSlotAvailability($pdo, $booking);
        }
        // If rejecting a pending booking or approving an already approved booking, no slot changes needed
        
        // Log the activity
        $details = "Booking {$action}d: Court {$booking['court_slot']}, Time: {$booking['time_slot']}";
        if ($booking['booking_type'] == 'monthly') {
            $details .= ", Month: {$booking['start_month']}, Duration: {$booking['duration']} months";
        } else {
            $details .= ", Date: {$booking['start_date']}";
        }
        
        logAdminActivity(
            $pdo, 
            $_SESSION['admin_id'], 
            $_SESSION['admin_username'], 
            $action . '_booking', 
            $booking_id, 
            $booking['booking_type'], 
            $oldStatus, 
            $newStatus, 
            $details
        );
        
        // Also update monthly_bookings table if it's a monthly booking
        if ($booking['booking_type'] == 'monthly') {
            $stmt = $pdo->prepare("UPDATE monthly_bookings SET status = ? WHERE booking_id = ?");
            $stmt->execute([$newStatus, $booking_id]);
        }
    }
    
    header('Location: dashboard.php');
    exit();
}

// Get bookings by status
$pendingBookings = $pdo->query("SELECT * FROM bookings WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$approvedBookings = $pdo->query("SELECT * FROM bookings WHERE status = 'approved' ORDER BY created_at DESC")->fetchAll();
$rejectedBookings = $pdo->query("SELECT * FROM bookings WHERE status = 'rejected' ORDER BY created_at DESC")->fetchAll();

// Get statistics
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$approvedCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'approved'")->fetchColumn();
$rejectedCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'rejected'")->fetchColumn();
$dailyCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_type = 'daily'")->fetchColumn();
$monthlyCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_type = 'monthly'")->fetchColumn();

// Get recent admin logs
$recentLogs = $pdo->query("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NBA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f6f9;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 1.8rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-card.pending h3 { color: #ffc107; }
        .stat-card.approved h3 { color: #28a745; }
        .stat-card.rejected h3 { color: #dc3545; }
        .stat-card.daily h3 { color: #17a2b8; }
        .stat-card.monthly h3 { color: #6f42c1; }
        
        .stat-card p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .bookings-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-header h2 {
            color: #333;
            margin: 0;
        }
        
        .section-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tab-button {
            flex: 1;
            padding: 15px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background: white;
            color: #333;
            border-bottom: 3px solid #667eea;
        }
        
        .tab-button:hover {
            background: #e9ecef;
        }
        
        .tab-content {
            display: none;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .bookings-table th,
        .bookings-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .bookings-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }
        
        .bookings-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background: #218838;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background: #c82333;
        }
        
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .no-bookings {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .logs-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .log-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .log-item:last-child {
            border-bottom: none;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .log-action {
            font-weight: 600;
            color: #333;
        }
        
        .log-time {
            font-size: 12px;
            color: #666;
        }
        
        .log-details {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }
        
        .log-admin {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .bookings-table {
                font-size: 14px;
            }
            
            .bookings-table th,
            .bookings-table td {
                padding: 10px 5px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="manage_slots.php" class="logout-btn" style="margin-left: 15px;">
                <i class="fas fa-cogs"></i> Manage Slots
            </a>
            <a href="create_admin.php" class="logout-btn" style="margin-left: 15px;">
                <i class="fas fa-user-plus"></i> Create Admin
            </a>
            <a href="logout.php" class="logout-btn" style="margin-left: 15px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <h3><?php echo $pendingCount; ?></h3>
                <p>Pending Approval</p>
            </div>
            <div class="stat-card approved">
                <h3><?php echo $approvedCount; ?></h3>
                <p>Approved</p>
            </div>
            <div class="stat-card rejected">
                <h3><?php echo $rejectedCount; ?></h3>
                <p>Rejected</p>
            </div>
            <div class="stat-card daily">
                <h3><?php echo $dailyCount; ?></h3>
                <p>Daily Bookings</p>
            </div>
            <div class="stat-card monthly">
                <h3><?php echo $monthlyCount; ?></h3>
                <p>Monthly Bookings</p>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <!-- Bookings Section -->
            <div class="bookings-section">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Booking Management</h2>
                </div>
                
                <div class="section-tabs">
                    <button class="tab-button active" onclick="showTab('pending')">
                        <i class="fas fa-clock"></i> Pending (<?php echo $pendingCount; ?>)
                    </button>
                    <button class="tab-button" onclick="showTab('approved')">
                        <i class="fas fa-check"></i> Approved (<?php echo $approvedCount; ?>)
                    </button>
                    <button class="tab-button" onclick="showTab('rejected')">
                        <i class="fas fa-times"></i> Rejected (<?php echo $rejectedCount; ?>)
                    </button>
                </div>
                
                <!-- Pending Bookings Tab -->
                <div id="pending" class="tab-content active">
                    <?php if (empty($pendingBookings)): ?>
                        <div class="no-bookings">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                            <h3>No pending bookings</h3>
                            <p>All bookings have been processed.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="bookings-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Court</th>
                                        <th>Date/Period</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingBookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['name']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($booking['email']); ?></small><br>
                                                <small><?php echo htmlspecialchars($booking['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $booking['booking_type'] == 'daily' ? 'status-approved' : 'status-pending'; ?>">
                                                    <?php echo ucfirst($booking['booking_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                Court <?php echo $booking['court_slot']; ?>
                                                <?php if ($booking['time_slot']): ?>
                                                    <br><small><?php echo $booking['time_slot']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($booking['booking_type'] == 'daily'): ?>
                                                    <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                                <?php else: ?>
                                                    <?php if ($booking['start_date']): ?>
                                                        <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                                    <?php else: ?>
                                                        <?php echo date('M Y', strtotime($booking['start_month'] . '-01')); ?>
                                                    <?php endif; ?>
                                                    <?php if ($booking['duration'] > 1): ?>
                                                        <br><small>(<?php echo $booking['duration']; ?> months)</small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $booking['payment_method'] == 'online' ? 'status-approved' : 'status-pending'; ?>">
                                                    <?php echo ucfirst($booking['payment_method']); ?>
                                                </span>
                                                <?php if ($booking['payment_screenshot']): ?>
                                                    <br><small><a href="../uploads/<?php echo $booking['payment_screenshot']; ?>" target="_blank">View Screenshot</a></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <button type="submit" name="action" value="approve" class="btn btn-approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="submit" name="action" value="reject" class="btn btn-reject">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Approved Bookings Tab -->
                <div id="approved" class="tab-content">
                    <?php if (empty($approvedBookings)): ?>
                        <div class="no-bookings">
                            <i class="fas fa-check-circle" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                            <h3>No approved bookings</h3>
                            <p>No bookings have been approved yet.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="bookings-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Court</th>
                                        <th>Date/Period</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approvedBookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['name']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($booking['email']); ?></small><br>
                                                <small><?php echo htmlspecialchars($booking['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $booking['booking_type'] == 'daily' ? 'status-approved' : 'status-pending'; ?>">
                                                    <?php echo ucfirst($booking['booking_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                Court <?php echo $booking['court_slot']; ?>
                                                <?php if ($booking['time_slot']): ?>
                                                    <br><small><?php echo $booking['time_slot']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($booking['booking_type'] == 'daily'): ?>
                                                    <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                                <?php else: ?>
                                                    <?php if ($booking['start_date']): ?>
                                                        <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                                    <?php else: ?>
                                                        <?php echo date('M Y', strtotime($booking['start_month'] . '-01')); ?>
                                                    <?php endif; ?>
                                                    <?php if ($booking['duration'] > 1): ?>
                                                        <br><small>(<?php echo $booking['duration']; ?> months)</small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $booking['payment_method'] == 'online' ? 'status-approved' : 'status-pending'; ?>">
                                                    <?php echo ucfirst($booking['payment_method']); ?>
                                                </span>
                                                <?php if ($booking['payment_screenshot']): ?>
                                                    <br><small><a href="../uploads/<?php echo $booking['payment_screenshot']; ?>" target="_blank">View Screenshot</a></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-approved">
                                                    Approved
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Rejected Bookings Tab -->
                <div id="rejected" class="tab-content">
                    <?php if (empty($rejectedBookings)): ?>
                        <div class="no-bookings">
                            <i class="fas fa-times-circle" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                            <h3>No rejected bookings</h3>
                            <p>No bookings have been rejected yet.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="bookings-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Court</th>
                                        <th>Date/Period</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rejectedBookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['name']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($booking['email']); ?></small><br>
                                                <small><?php echo htmlspecialchars($booking['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $booking['booking_type'] == 'daily' ? 'status-approved' : 'status-pending'; ?>">
                                                    <?php echo ucfirst($booking['booking_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                Court <?php echo $booking['court_slot']; ?>
                                                <?php if ($booking['time_slot']): ?>
                                                    <br><small><?php echo $booking['time_slot']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($booking['booking_type'] == 'daily'): ?>
                                                    <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                                <?php else: ?>
                                                    <?php if ($booking['start_date']): ?>
                                                        <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                                    <?php else: ?>
                                                        <?php echo date('M Y', strtotime($booking['start_month'] . '-01')); ?>
                                                    <?php endif; ?>
                                                    <?php if ($booking['duration'] > 1): ?>
                                                        <br><small>(<?php echo $booking['duration']; ?> months)</small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $booking['payment_method'] == 'online' ? 'status-approved' : 'status-pending'; ?>">
                                                    <?php echo ucfirst($booking['payment_method']); ?>
                                                </span>
                                                <?php if ($booking['payment_screenshot']): ?>
                                                    <br><small><a href="../uploads/<?php echo $booking['payment_screenshot']; ?>" target="_blank">View Screenshot</a></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-rejected">
                                                    Rejected
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Admin Logs Section -->
            <div class="logs-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Activity</h2>
                </div>
                
                <?php if (empty($recentLogs)): ?>
                    <div class="no-bookings">
                        <i class="fas fa-history" style="font-size: 2rem; color: #ddd; margin-bottom: 15px;"></i>
                        <p>No activity logs yet.</p>
                    </div>
                <?php else: ?>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="log-item">
                                <div class="log-header">
                                    <span class="log-action">
                                        <?php 
                                        $actionText = str_replace('_', ' ', $log['action']);
                                        echo ucwords($actionText);
                                        ?>
                                    </span>
                                    <span class="log-time">
                                        <?php echo date('M d, H:i', strtotime($log['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="log-details">
                                    <?php echo htmlspecialchars($log['details']); ?>
                                </div>
                                <div class="log-admin">
                                    by <?php echo htmlspecialchars($log['admin_username']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => button.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
