<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
        $stmt->execute([$booking_id]);
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$booking_id]);
    }
    
    header('Location: dashboard.php');
    exit();
}

// Get all bookings
$stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
$bookings = $stmt->fetchAll();

// Get statistics
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$approvedBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'approved'")->fetchColumn();
$rejectedBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'rejected'")->fetchColumn();
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 1.1rem;
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
        }
        
        .section-header h2 {
            color: #333;
            margin: 0;
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
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
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
            <div class="stat-card">
                <h3><?php echo $totalBookings; ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pendingBookings; ?></h3>
                <p>Pending Approval</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $approvedBookings; ?></h3>
                <p>Approved</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $rejectedBookings; ?></h3>
                <p>Rejected</p>
            </div>
        </div>
        
        <!-- Bookings Table -->
        <div class="bookings-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> All Bookings</h2>
            </div>
            
            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                    <h3>No bookings found</h3>
                    <p>Bookings will appear here once customers submit them.</p>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
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
                                    <td>Court <?php echo $booking['court_slot']; ?></td>
                                    <td>
                                        <?php if ($booking['booking_type'] == 'daily'): ?>
                                            <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                        <?php else: ?>
                                            <?php echo date('M Y', strtotime($booking['start_month'] . '-01')); ?>
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
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] == 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-approve">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn btn-reject">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #666; font-style: italic;">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
