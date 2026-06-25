CREATE TABLE IF NOT EXISTS install_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM('page_view', 'install_clicked', 'installed', 'uninstalled', 'ios_instructions_shown') NOT NULL,
    device_type ENUM('android', 'ios', 'desktop', 'other') NOT NULL,
    browser VARCHAR(50),
    source VARCHAR(100) DEFAULT 'direct',
    user_ip VARCHAR(45),
    user_agent TEXT,
    referrer TEXT,
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_device_type (device_type),
    INDEX idx_created_at (created_at),
    INDEX idx_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
