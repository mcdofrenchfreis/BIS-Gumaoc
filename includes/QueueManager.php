<?php
class QueueManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Generate a new queue ticket
     */
    public function generateTicket($service_id, $customer_name, $mobile_number = null, $user_id = null, $purpose = null, $priority_level = 'normal') {
        try {
            // Get service details
            $service_stmt = $this->pdo->prepare("SELECT * FROM queue_services WHERE id = ? AND is_active = 1");
            $service_stmt->execute([$service_id]);
            $service = $service_stmt->fetch();
            
            if (!$service) {
                return ['success' => false, 'message' => 'Service not found or inactive'];
            }
            
            // Generate ticket number
            $date_code = date('Ymd');
            $service_code = $service['service_code'];
            
            // Get next sequence number for today
            $seq_stmt = $this->pdo->prepare("SELECT COUNT(*) + 1 as next_seq FROM queue_tickets WHERE ticket_number LIKE ? AND DATE(created_at) = CURDATE()");
            $seq_stmt->execute(["{$service_code}-{$date_code}-%"]);
            $sequence = $seq_stmt->fetchColumn();
            
            $ticket_number = sprintf("%s-%s-%03d", $service_code, $date_code, $sequence);
            
            // Calculate queue position
            $position_stmt = $this->pdo->prepare("SELECT COUNT(*) + 1 as position FROM queue_tickets WHERE service_id = ? AND status = 'waiting' AND DATE(created_at) = CURDATE()");
            $position_stmt->execute([$service_id]);
            $queue_position = $position_stmt->fetchColumn();
            
            // Calculate estimated time
            $estimated_minutes = ($queue_position - 1) * $service['estimated_time'];
            $estimated_time = date('Y-m-d H:i:s', strtotime("+{$estimated_minutes} minutes"));
            
            // Insert ticket
            $insert_stmt = $this->pdo->prepare("
                INSERT INTO queue_tickets (
                    ticket_number, service_id, customer_name, mobile_number, user_id, 
                    purpose, priority_level, queue_position, estimated_time
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $insert_stmt->execute([
                $ticket_number,
                $service_id,
                $customer_name,
                $mobile_number,
                $user_id,
                $purpose,
                $priority_level,
                $queue_position,
                $estimated_time
            ]);
            
            $ticket_id = $this->pdo->lastInsertId();
            
            return [
                'success' => true,
                'ticket_id' => $ticket_id,
                'ticket_number' => $ticket_number,
                'queue_position' => $queue_position,
                'estimated_time' => date('g:i A', strtotime($estimated_time)),
                'service_name' => $service['service_name']
            ];
            
        } catch (Exception $e) {
            error_log("Queue ticket generation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error generating ticket: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get ticket status
     */
    public function getTicketStatus($ticket_number) {
        $stmt = $this->pdo->prepare("
            SELECT qt.*, qs.service_name, qs.estimated_time as service_time
            FROM queue_tickets qt 
            JOIN queue_services qs ON qt.service_id = qs.id 
            WHERE qt.ticket_number = ?
        ");
        $stmt->execute([$ticket_number]);
        return $stmt->fetch();
    }
    
    /**
     * Get queue statistics
     */
    public function getQueueStats($service_id = null) {
        $where = $service_id ? "WHERE qt.service_id = ?" : "";
        $params = $service_id ? [$service_id] : [];
        
        $stmt = $this->pdo->prepare("
            SELECT qt.status, COUNT(*) as count 
            FROM queue_tickets qt 
            {$where} AND DATE(qt.created_at) = CURDATE() 
            GROUP BY qt.status
        ");
        $stmt->execute($params);
        
        $stats = [];
        while ($row = $stmt->fetch()) {
            $stats[$row['status']] = $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get waiting tickets
     */
    public function getWaitingTickets($service_id = null) {
        $where = $service_id ? "WHERE qt.service_id = ?" : "";
        $params = $service_id ? [$service_id] : [];
        
        $stmt = $this->pdo->prepare("
            SELECT qt.*, qs.service_name 
            FROM queue_tickets qt 
            JOIN queue_services qs ON qt.service_id = qs.id 
            {$where} AND qt.status = 'waiting' 
            AND DATE(qt.created_at) = CURDATE() 
            ORDER BY qt.queue_position ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>