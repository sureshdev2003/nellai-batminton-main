<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Function to recalculate all slot availability based on approved bookings
function recalculateSlotAvailability($pdo) {
    try {
        // First, reset all slots to maximum capacity
        $stmt = $pdo->prepare("UPDATE slots SET available_members = max_members");
        $stmt->execute();
        
        // Get all approved monthly bookings
        $stmt = $pdo->prepare("
            SELECT court_slot, time_slot, members_count 
            FROM bookings 
            WHERE booking_type = 'monthly' AND status = 'approved'
        ");
        $stmt->execute();
        $monthlyBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Update slots based on approved monthly bookings
        foreach ($monthlyBookings as $booking) {
            $stmt = $pdo->prepare("
                UPDATE slots 
                SET available_members = GREATEST(0, available_members - ?) 
                WHERE court_number = ? AND time_slot = ?
            ");
            $stmt->execute([
                $booking['members_count'],
                $booking['court_slot'],
                $booking['time_slot']
            ]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to recalculate slot availability: " . $e->getMessage());
        return false;
    }
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'recalculate':
                if (recalculateSlotAvailability($pdo)) {
                    $message = "Slot availability recalculated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to recalculate slot availability.";
                    $messageType = "error";
                }
                break;
                
            case 'reset':
                try {
                    $stmt = $pdo->prepare("UPDATE slots SET available_members = max_members");
                    $stmt->execute();
                    $message = "All slots reset to maximum capacity!";
                    $messageType = "success";
                } catch (Exception $e) {
                    $message = "Failed to reset slots: " . $e->getMessage();
                    $messageType = "error";
                }
                break;
        }
    }
}

// Get current slot statistics
$totalSlots = $pdo->query("SELECT COUNT(*) FROM slots")->fetchColumn();
$fullSlots = $pdo->query("SELECT COUNT(*) FROM slots WHERE available_members = 0")->fetchColumn();
$availableSlots = $pdo->query("SELECT COUNT(*) FROM slots WHERE available_members > 0")->fetchColumn();

// Get approved monthly bookings count
$approvedMonthlyBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_type = 'monthly' AND status = 'approved'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Slots - NBA Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .action-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cogs"></i> Manage Slot Availability</h1>
            <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalSlots; ?></div>
                <div>Total Slots</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $availableSlots; ?></div>
                <div>Available Slots</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $fullSlots; ?></div>
                <div>Full Slots</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $approvedMonthlyBookings; ?></div>
                <div>Approved Monthly Bookings</div>
            </div>
        </div>

        <div class="actions">
            <div class="action-card">
                <h3><i class="fas fa-calculator"></i> Recalculate Availability</h3>
                <p>Recalculate slot availability based on all approved monthly bookings. This ensures the slots table is accurate.</p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="recalculate">
                    <button type="submit" class="btn" onclick="return confirm('Are you sure you want to recalculate slot availability?')">
                        <i class="fas fa-calculator"></i> Recalculate
                    </button>
                </form>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-undo"></i> Reset All Slots</h3>
                <p>Reset all slots to maximum capacity (6 members). Use this only if you need to start fresh.</p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="reset">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reset all slots? This will clear all availability restrictions.')">
                        <i class="fas fa-undo"></i> Reset All
                    </button>
                </form>
            </div>
        </div>

        <div class="action-card">
            <h3><i class="fas fa-info-circle"></i> How It Works</h3>
            <ul>
                <li><strong>Daily Bookings:</strong> Availability is calculated dynamically based on approved daily bookings for specific dates.</li>
                <li><strong>Monthly Bookings:</strong> When approved, they reduce the available members in the slots table for the entire duration.</li>
                <li><strong>Recalculation:</strong> Use this to ensure the slots table accurately reflects all approved monthly bookings.</li>
                <li><strong>Reset:</strong> Use this only if you need to start fresh and clear all availability restrictions.</li>
            </ul>
        </div>
    </div>
</body>
</html>
