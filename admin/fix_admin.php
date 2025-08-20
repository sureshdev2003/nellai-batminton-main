<?php
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'create_admin') {
        $username = 'admin';
        $email = 'admin@nba.com';
        $password = 'admin123';
        
        try {
            // Check if admin already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetchColumn() > 0) {
                // Update existing admin password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
                $stmt->execute([$hashed_password, $username]);
                $message = "Admin password updated successfully!<br>Username: admin<br>Password: admin123";
            } else {
                // Create new admin
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                $message = "Admin user created successfully!<br>Username: admin<br>Password: admin123";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'clear_admins') {
        try {
            // Clear all existing admins
            $stmt = $pdo->prepare("DELETE FROM admins");
            $stmt->execute();
            $message = "All admin users cleared successfully!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get current admin users
try {
    $stmt = $pdo->query("SELECT username, email, created_at FROM admins ORDER BY created_at");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admins = [];
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Access - NBA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f6f8;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .action-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            margin-right: 15px;
            margin-bottom: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        .admin-list {
            list-style: none;
        }
        
        .admin-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        
        .admin-username {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .admin-email {
            color: #666;
            font-size: 14px;
        }
        
        .admin-date {
            color: #999;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .nav-links {
            margin-top: 20px;
            text-align: center;
        }
        
        .nav-links a {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        
        .nav-links a:hover {
            background: #f0f2ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tools"></i> Fix Admin Access</h1>
            <p>Nellai Badminton Academy - Admin System Repair</p>
        </div>
        
        <div class="warning">
            <strong><i class="fas fa-exclamation-triangle"></i> Important:</strong> 
            This tool will fix your admin access issues. Use it only if you cannot login to the admin system.
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="action-section">
            <h2 class="section-title"><i class="fas fa-user-shield"></i> Fix Admin Access</h2>
            
            <p style="margin-bottom: 20px; color: #666;">
                Click the button below to create/update the admin user with the correct password.
            </p>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="create_admin">
                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i> Create/Update Admin (admin/admin123)
                </button>
            </form>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="clear_admins">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure? This will delete ALL admin users!')">
                    <i class="fas fa-trash"></i> Clear All Admins
                </button>
            </form>
        </div>
        
        <div class="action-section">
            <h2 class="section-title"><i class="fas fa-users"></i> Current Admin Users</h2>
            
            <?php if (empty($admins)): ?>
                <p style="color: #666; text-align: center;">No admin users found in database.</p>
            <?php else: ?>
                <ul class="admin-list">
                    <?php foreach ($admins as $admin): ?>
                        <li class="admin-item">
                            <div class="admin-username">
                                <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin['username']); ?>
                            </div>
                            <div class="admin-email">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($admin['email']); ?>
                            </div>
                            <div class="admin-date">
                                <i class="fas fa-calendar"></i> Created: <?php echo date('M j, Y', strtotime($admin['created_at'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="nav-links">
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Try Login</a>
            <a href="create_admin.php"><i class="fas fa-user-plus"></i> Create Admin</a>
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
        </div>
    </div>
</body>
</html>
