<?php
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        try {
            // Check if admin exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetchColumn() == 0) {
                $error = "Admin user '$username' not found";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
                $stmt->execute([$hashed_password, $username]);
                
                $message = "Password for admin '$username' updated successfully!";
                
                // Clear form
                $_POST = array();
            }
        } catch (Exception $e) {
            $error = "Error updating password: " . $e->getMessage();
        }
    }
}

// Get list of existing admins
try {
    $stmt = $pdo->query("SELECT username, email FROM admins ORDER BY username");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admins = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - NBA</title>
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
        
        .nav-links {
            margin-top: 15px;
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
        
        .form-section {
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
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
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-key"></i> Reset Admin Password</h1>
            <p>Nellai Badminton Academy - Admin Password Management</p>
            <div class="nav-links">
                <a href="logout.php"><i class="fas fa-sign-in-alt"></i> Logout</a>
                <a href="create_admin.php"><i class="fas fa-user-plus"></i> Create Admin</a>
                <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            </div>
        </div>
        
        <div class="warning">
            <strong><i class="fas fa-exclamation-triangle"></i> Warning:</strong> 
            This will change the password for the selected admin user. Make sure you have the new password ready.
        </div>
        
        <div class="form-section">
            <h2 class="section-title"><i class="fas fa-key"></i> Reset Admin Password</h2>
            
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Select Admin User *</label>
                    <select id="username" name="username" required>
                        <option value="">Choose an admin user</option>
                        <?php foreach ($admins as $admin): ?>
                            <option value="<?php echo htmlspecialchars($admin['username']); ?>">
                                <?php echo htmlspecialchars($admin['username']); ?> (<?php echo htmlspecialchars($admin['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <small style="color: #666; font-size: 12px;">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>
        
        <div class="form-section">
            <h2 class="section-title"><i class="fas fa-users"></i> Available Admin Users</h2>
            
            <?php if (empty($admins)): ?>
                <p style="color: #666; text-align: center;">No admin users found.</p>
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
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
