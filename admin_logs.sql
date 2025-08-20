-- Admin Activity Logs Table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    admin_username VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    booking_id INT,
    booking_type ENUM('daily', 'monthly') NULL,
    old_status VARCHAR(20),
    new_status VARCHAR(20),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Index for better performance
CREATE INDEX idx_admin_logs_admin_id ON admin_logs(admin_id);
CREATE INDEX idx_admin_logs_booking_id ON admin_logs(booking_id);
CREATE INDEX idx_admin_logs_created_at ON admin_logs(created_at);
