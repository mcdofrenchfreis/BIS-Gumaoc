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
     * Get queue status for all services (enhanced for kiosk display)
     */
    public function getQueueStatus() {
        $stmt = $this->pdo->prepare("
            SELECT 
                qs.id,
                qs.service_name,
                qs.service_code,
                qs.estimated_time,
                COUNT(CASE WHEN qt.status = 'waiting' THEN 1 END) as waiting_count,
                COUNT(CASE WHEN qt.status = 'serving' THEN 1 END) as serving_count,
                COUNT(CASE WHEN qt.status = 'completed' THEN 1 END) as completed_count,
                AVG(CASE WHEN qt.status = 'completed' AND qt.served_at IS NOT NULL AND qt.completed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, qt.served_at, qt.completed_at) END) as avg_service_time
            FROM queue_services qs
            LEFT JOIN queue_tickets qt ON qs.id = qt.service_id AND DATE(qt.created_at) = CURDATE()
            WHERE qs.is_active = 1
            GROUP BY qs.id, qs.service_name, qs.service_code, qs.estimated_time
            ORDER BY qs.service_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Auto-generate ticket for form submissions (guest-friendly)
     */
    public function generateTicketForForm($form_type, $customer_name, $mobile_number = null, $purpose = null) {
        // Map form types to service IDs
        $service_mapping = [
            'certificate_request' => 5, // General Services
            'resident_registration' => 5, // General Services
            'business_application' => 6, // Business Permit
            'brgy_clearance' => 1, // Barangay Clearance
            'brgy_indigency' => 2, // Barangay Indigency
            'tricycle_permit' => 3, // Tricycle Permit
            'proof_residency' => 4, // Proof of Residency
        ];
        
        $service_id = $service_mapping[$form_type] ?? 5; // Default to General Services
        $full_purpose = $purpose ? "{$form_type}: {$purpose}" : $form_type;
        
        return $this->generateTicket(
            $service_id,
            $customer_name,
            $mobile_number,
            null, // user_id (guest)
            $full_purpose,
            'normal' // priority level
        );
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
    
    /**
     * Get currently serving tickets (for kiosk display)
     */
    public function getCurrentlyServing() {
        $stmt = $this->pdo->prepare("
            SELECT 
                qt.ticket_number,
                qt.customer_name,
                qs.service_name,
                qs.service_code,
                qc.counter_name,
                qt.served_at
            FROM queue_tickets qt
            JOIN queue_services qs ON qt.service_id = qs.id
            LEFT JOIN queue_counters qc ON qc.current_ticket_id = qt.id
            WHERE qt.status = 'serving' 
            AND DATE(qt.created_at) = CURDATE()
            ORDER BY qt.served_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get next tickets in queue (for kiosk display)
     */
    public function getNextInQueue($limit = 5) {
        // Ensure limit is a positive integer and within reasonable bounds
        $limit = max(1, min(100, (int)$limit));
        
        $stmt = $this->pdo->prepare("
            SELECT 
                qt.ticket_number,
                qt.customer_name,
                qs.service_name,
                qs.service_code,
                qt.queue_position,
                qt.estimated_time
            FROM queue_tickets qt
            JOIN queue_services qs ON qt.service_id = qs.id
            WHERE qt.status = 'waiting' 
            AND DATE(qt.created_at) = CURDATE()
            ORDER BY qt.queue_position ASC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Call next ticket for a specific service counter
     */
    public function callNextTicket($counter_id) {
        try {
            // Get counter information
            $counter_stmt = $this->pdo->prepare("SELECT * FROM queue_counters WHERE id = ? AND is_active = 1");
            $counter_stmt->execute([$counter_id]);
            $counter = $counter_stmt->fetch();
            
            if (!$counter) {
                return ['success' => false, 'message' => 'Counter not found or inactive'];
            }
            
            // Counter 1 (All Certificates) - Handle service IDs 1,2,3,4 (all certificate types)
            if ($counter['id'] == 1) {
                $ticket_stmt = $this->pdo->prepare("
                    SELECT * FROM queue_tickets 
                    WHERE service_id IN (1,2,3,4) AND status = 'waiting' 
                    AND DATE(created_at) = CURDATE()
                    ORDER BY queue_position ASC 
                    LIMIT 1
                ");
                $ticket_stmt->execute();
                $ticket = $ticket_stmt->fetch();
            } else {
                // For other counters, get tickets for their specific service
                $ticket_stmt = $this->pdo->prepare("
                    SELECT * FROM queue_tickets 
                    WHERE service_id = ? AND status = 'waiting' 
                    AND DATE(created_at) = CURDATE()
                    ORDER BY queue_position ASC 
                    LIMIT 1
                ");
                $ticket_stmt->execute([$counter['service_id']]);
                $ticket = $ticket_stmt->fetch();
                
                // If no tickets for specific service, try to get any waiting ticket
                // (This is helpful for General Services counters that can handle any service)
                if (!$ticket && $counter['service_id'] == 5) { // General Services counter
                    $ticket_stmt = $this->pdo->prepare("
                        SELECT * FROM queue_tickets 
                        WHERE status = 'waiting' 
                        AND DATE(created_at) = CURDATE()
                        ORDER BY queue_position ASC 
                        LIMIT 1
                    ");
                    $ticket_stmt->execute();
                    $ticket = $ticket_stmt->fetch();
                }
            }
            
            if (!$ticket) {
                // Check if there are any waiting tickets at all
                $debug_stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as total_waiting
                    FROM queue_tickets 
                    WHERE status = 'waiting' AND DATE(created_at) = CURDATE()
                ");
                $debug_stmt->execute();
                $debug_info = $debug_stmt->fetch();
                
                if ($debug_info['total_waiting'] > 0) {
                    // There are waiting tickets, but not for this counter's service
                    if ($counter['id'] == 1) {
                        $message = "No certificate requests are currently waiting in the queue.";
                    } elseif ($counter['service_id'] == 6) {
                        $message = "No business permit applications are currently waiting in the queue.";
                    } else {
                        $message = "No customers are currently waiting for this service.";
                    }
                } else {
                    $message = "No customers are waiting in the queue today.";
                }
                
                return ['success' => false, 'message' => $message];
            }
            
            // Update ticket status to serving
            $update_ticket = $this->pdo->prepare("
                UPDATE queue_tickets 
                SET status = 'serving', called_at = NOW(), served_at = NOW() 
                WHERE id = ?
            ");
            $update_ticket->execute([$ticket['id']]);
            
            // Update counter with current ticket
            $update_counter = $this->pdo->prepare("
                UPDATE queue_counters 
                SET current_ticket_id = ?, last_called_at = NOW() 
                WHERE id = ?
            ");
            $update_counter->execute([$ticket['id'], $counter_id]);
            
            return [
                'success' => true,
                'ticket' => $ticket,
                'counter' => $counter
            ];
            
        } catch (Exception $e) {
            error_log("Error calling next ticket: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error calling next ticket: ' . $e->getMessage()];
        }
    }
    
    /**
     * Complete current ticket for a counter
     */
    public function completeTicket($counter_id, $notes = null) {
        try {
            // Get counter and current ticket
            $stmt = $this->pdo->prepare("
                SELECT qc.*, qt.id as ticket_id 
                FROM queue_counters qc 
                LEFT JOIN queue_tickets qt ON qc.current_ticket_id = qt.id 
                WHERE qc.id = ?
            ");
            $stmt->execute([$counter_id]);
            $result = $stmt->fetch();
            
            if (!$result || !$result['ticket_id']) {
                return ['success' => false, 'message' => 'No active ticket for this counter'];
            }
            
            // Update ticket to completed
            $update_ticket = $this->pdo->prepare("
                UPDATE queue_tickets 
                SET status = 'completed', completed_at = NOW(), notes = ? 
                WHERE id = ?
            ");
            $update_ticket->execute([$notes, $result['ticket_id']]);
            
            // Clear counter's current ticket
            $update_counter = $this->pdo->prepare("
                UPDATE queue_counters 
                SET current_ticket_id = NULL 
                WHERE id = ?
            ");
            $update_counter->execute([$counter_id]);
            
            return ['success' => true, 'message' => 'Ticket completed successfully'];
            
        } catch (Exception $e) {
            error_log("Error completing ticket: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error completing ticket'];
        }
    }
}
?>