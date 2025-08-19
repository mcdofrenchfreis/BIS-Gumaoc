<?php
class AdminLogger {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureLogTableExists();
    }
    
    private function ensureLogTableExists() {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'admin_logs'");
            if ($stmt->rowCount() == 0) {
                // Create table if it doesn't exist
                $sql = "CREATE TABLE admin_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    admin_id VARCHAR(100) DEFAULT 'system',
                    action_type VARCHAR(50) NOT NULL,
                    target_type VARCHAR(50) NOT NULL,
                    target_id INT NULL,
                    description TEXT NOT NULL,
                    details JSON NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_admin_id (admin_id),
                    INDEX idx_action_type (action_type),
                    INDEX idx_target_type (target_type),
                    INDEX idx_created_at (created_at)
                )";
                $this->pdo->exec($sql);
            }
        } catch (Exception $e) {
            error_log("Failed to ensure admin_logs table exists: " . $e->getMessage());
        }
    }
    
    public function log($actionType, $targetType, $description, $targetId = null, $details = null) {
        try {
            $adminId = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true 
                ? ($_SESSION['admin_username'] ?? 'admin') 
                : 'system';
            
            $ipAddress = $this->getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_logs (admin_id, action_type, target_type, target_id, description, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $detailsJson = $details ? json_encode($details) : null;
            
            $result = $stmt->execute([
                $adminId,
                $actionType,
                $targetType,
                $targetId,
                $description,
                $detailsJson,
                $ipAddress,
                $userAgent
            ]);
            
            // Debug: Log to PHP error log
            if (!$result) {
                error_log("AdminLogger: Failed to insert log entry");
                error_log("AdminLogger Error Info: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Failed to log admin action: " . $e->getMessage());
            return false;
        }
    }
    
    private function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    public function getLogs($limit = 100, $offset = 0, $filters = []) {
        try {
            $whereConditions = [];
            $params = [];
            
            if (!empty($filters['admin_id'])) {
                $whereConditions[] = "admin_id = ?";
                $params[] = $filters['admin_id'];
            }
            
            if (!empty($filters['action_type'])) {
                $whereConditions[] = "action_type = ?";
                $params[] = $filters['action_type'];
            }
            
            if (!empty($filters['target_type'])) {
                $whereConditions[] = "target_type = ?";
                $params[] = $filters['target_type'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }
            
            $whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";
            
            $sql = "SELECT * FROM admin_logs $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get admin logs: " . $e->getMessage());
            return [];
        }
    }
    
    public function getLogCount($filters = []) {
        try {
            $whereConditions = [];
            $params = [];
            
            if (!empty($filters['admin_id'])) {
                $whereConditions[] = "admin_id = ?";
                $params[] = $filters['admin_id'];
            }
            
            if (!empty($filters['action_type'])) {
                $whereConditions[] = "action_type = ?";
                $params[] = $filters['action_type'];
            }
            
            if (!empty($filters['target_type'])) {
                $whereConditions[] = "target_type = ?";
                $params[] = $filters['target_type'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }
            
            $whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";
            
            $sql = "SELECT COUNT(*) FROM admin_logs $whereClause";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Failed to get admin logs count: " . $e->getMessage());
            return 0;
        }
    }
    
    // Convenience methods
    public function logStatusUpdate($targetType, $targetId, $oldStatus, $newStatus, $additionalDetails = []) {
        $details = array_merge([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'timestamp' => date('Y-m-d H:i:s')
        ], $additionalDetails);
        
        return $this->log(
            'status_update',
            $targetType,
            "Updated {$targetType} ID #{$targetId} status from '{$oldStatus}' to '{$newStatus}'",
            $targetId,
            $details
        );
    }
    
    public function logPrintAction($targetType, $targetId, $printType = 'certificate', $additionalDetails = []) {
        $details = array_merge([
            'print_type' => $printType,
            'print_timestamp' => date('Y-m-d H:i:s')
        ], $additionalDetails);
        
        return $this->log(
            'print_action',
            $targetType,
            "Printed {$printType} for {$targetType} ID #{$targetId}",
            $targetId,
            $details
        );
    }
    
    public function logAdminLogin($username, $success = true) {
        $status = $success ? 'successful' : 'failed';
        return $this->log(
            'admin_login',
            'admin_auth',
            "Admin login {$status} for username: {$username}",
            null,
            ['username' => $username, 'success' => $success]
        );
    }
    
    public function logAdminLogout($username) {
        return $this->log(
            'admin_logout',
            'admin_auth',
            "Admin logout for username: {$username}",
            null,
            ['username' => $username]
        );
    }
}
?>