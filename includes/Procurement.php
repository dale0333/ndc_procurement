<?php
require_once __DIR__ . '/Database.php';

class Procurement {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        // Generate reference number
        $year = date('Y');
        $count = $this->db->selectOne("SELECT COUNT(*) as count FROM procurements WHERE YEAR(created_at) = :year", ['year' => $year]);
        $data['reference_number'] = sprintf('PROC-%s-%04d', $year, $count['count'] + 1);
        
        $procurementId = $this->db->insert('procurements', $data);
        
        // Log activity
        $this->logActivity($procurementId, $_SESSION['user_id'], 'create', 'Procurement request created');
        
        return $procurementId;
    }
    
    public function delete($id) {
        try {
            // Start transaction
            $this->db->query("START TRANSACTION");
            
            // Delete associated stage data
            $tables = [
                'budget_formulation',
                'budget_review', 
                'procurement_planning',
                'contract_execution',
                'disbursements', // Note: table name is plural
                'monitoring_reports', // Note: table name is plural
                'audits' // Note: table name is plural
            ];
            
            foreach ($tables as $table) {
                $this->db->query("DELETE FROM {$table} WHERE procurement_id = :id", ['id' => $id]);
            }
            
            // Delete comments
            $this->db->query("DELETE FROM comments WHERE procurement_id = :id", ['id' => $id]);
            
            // Delete activity log (table name is activity_logs, not activity_log)
            $this->db->query("DELETE FROM activity_logs WHERE procurement_id = :id", ['id' => $id]);
            
            // Delete the procurement itself
            $this->db->query("DELETE FROM procurements WHERE id = :id", ['id' => $id]);
            
            // Commit transaction
            $this->db->query("COMMIT");
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $this->db->query("ROLLBACK");
            error_log("Delete procurement error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        return $this->db->selectOne(
            "SELECT p.*, u.full_name as created_by_name, u.department as creator_department 
             FROM procurements p 
             JOIN users u ON p.created_by = u.id 
             WHERE p.id = :id",
            ['id' => $id]
        );
    }
    
    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'p.status = :status';
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['department'])) {
            $where[] = 'p.department = :department';
            $params['department'] = $filters['department'];
        }
        
        if (!empty($filters['stage'])) {
            $where[] = 'p.current_stage = :stage';
            $params['stage'] = $filters['stage'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        return $this->db->select(
            "SELECT p.*, u.full_name as created_by_name 
             FROM procurements p 
             JOIN users u ON p.created_by = u.id 
             WHERE $whereClause 
             ORDER BY p.created_at DESC",
            $params
        );
    }
    
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('procurements', $data, 'id = :id', ['id' => $id]);
    }
    
    public function updateStage($id, $newStage) {
        $this->update($id, ['current_stage' => $newStage]);
        $this->logActivity($id, $_SESSION['user_id'], 'stage_change', "Stage changed to: $newStage");
    }
    
    public function updateStatus($id, $newStatus) {
        $data = ['status' => $newStatus];
        if ($newStatus === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        $this->update($id, $data);
        $this->logActivity($id, $_SESSION['user_id'], 'status_change', "Status changed to: $newStatus");
    }
    
    // Stage-specific methods
    public function saveBudgetFormulation($data) {
        $existing = $this->db->selectOne(
            "SELECT id FROM budget_formulation WHERE procurement_id = :procurement_id",
            ['procurement_id' => $data['procurement_id']]
        );
        
        if ($existing) {
            return $this->db->update('budget_formulation', $data, 'procurement_id = :id', ['id' => $data['procurement_id']]);
        } else {
            return $this->db->insert('budget_formulation', $data);
        }
    }
    
    public function saveBudgetReview($data) {
        $existing = $this->db->selectOne(
            "SELECT id FROM budget_review WHERE procurement_id = :procurement_id",
            ['procurement_id' => $data['procurement_id']]
        );
        
        if ($existing) {
            $id = $this->db->update('budget_review', $data, 'procurement_id = :id', ['id' => $data['procurement_id']]);
        } else {
            $id = $this->db->insert('budget_review', $data);
        }
        
        // Update procurement estimated value
        if (!empty($data['approved_amount'])) {
            $this->update($data['procurement_id'], ['estimated_value' => $data['approved_amount']]);
        }
        
        return $id;
    }
    
    public function saveProcurementPlanning($data) {
        $existing = $this->db->selectOne(
            "SELECT id FROM procurement_planning WHERE procurement_id = :procurement_id",
            ['procurement_id' => $data['procurement_id']]
        );
        
        if ($existing) {
            return $this->db->update('procurement_planning', $data, 'procurement_id = :id', ['id' => $data['procurement_id']]);
        } else {
            return $this->db->insert('procurement_planning', $data);
        }
    }
    
    public function saveContractExecution($data) {
        $existing = $this->db->selectOne(
            "SELECT id FROM contract_execution WHERE procurement_id = :procurement_id",
            ['procurement_id' => $data['procurement_id']]
        );
        
        if ($existing) {
            $id = $this->db->update('contract_execution', $data, 'procurement_id = :id', ['id' => $data['procurement_id']]);
        } else {
            $id = $this->db->insert('contract_execution', $data);
        }
        
        // Update actual value
        if (!empty($data['contract_amount'])) {
            $this->update($data['procurement_id'], ['actual_value' => $data['contract_amount']]);
        }
        
        return $id;
    }
    
    public function saveDisbursement($data) {
        return $this->db->insert('disbursements', $data);
    }
    
    public function saveMonitoring($data) {
        return $this->db->insert('monitoring_reports', $data);
    }
    
    public function saveAudit($data) {
        $existing = $this->db->selectOne(
            "SELECT id FROM audits WHERE procurement_id = :procurement_id",
            ['procurement_id' => $data['procurement_id']]
        );
        
        if ($existing) {
            $id = $this->db->update('audits', $data, 'procurement_id = :id', ['id' => $data['procurement_id']]);
        } else {
            $id = $this->db->insert('audits', $data);
        }
        
        // Mark procurement as completed
        $this->updateStatus($data['procurement_id'], 'completed');
        
        return $id;
    }
    
    // Get stage data
    public function getStageData($procurementId, $table) {
        return $this->db->selectOne(
            "SELECT * FROM $table WHERE procurement_id = :procurement_id ORDER BY id DESC LIMIT 1",
            ['procurement_id' => $procurementId]
        );
    }
    
    public function getAllStageData($procurementId) {
        return [
            'budget_formulation' => $this->getStageData($procurementId, 'budget_formulation'),
            'budget_review' => $this->getStageData($procurementId, 'budget_review'),
            'procurement_planning' => $this->getStageData($procurementId, 'procurement_planning'),
            'contract_execution' => $this->getStageData($procurementId, 'contract_execution'),
            'disbursement' => $this->db->select("SELECT * FROM disbursements WHERE procurement_id = :id ORDER BY id DESC", ['id' => $procurementId]),
            'monitoring' => $this->db->select("SELECT * FROM monitoring_reports WHERE procurement_id = :id ORDER BY id DESC", ['id' => $procurementId]),
            'audit' => $this->getStageData($procurementId, 'audits')
        ];
    }
    
    // Statistics
    public function getStatistics() {
        $stats = [];
        
        $stats['total'] = $this->db->selectOne("SELECT COUNT(*) as count FROM procurements")['count'];
        $stats['in_progress'] = $this->db->selectOne("SELECT COUNT(*) as count FROM procurements WHERE status = 'in_progress'")['count'];
        $stats['completed'] = $this->db->selectOne("SELECT COUNT(*) as count FROM procurements WHERE status = 'completed'")['count'];
        $stats['total_value'] = $this->db->selectOne("SELECT SUM(actual_value) as total FROM procurements WHERE status = 'completed'")['total'] ?? 0;
        
        $stats['by_stage'] = $this->db->select("SELECT current_stage, COUNT(*) as count FROM procurements WHERE status = 'in_progress' GROUP BY current_stage");
        $stats['by_department'] = $this->db->select("SELECT department, COUNT(*) as count FROM procurements GROUP BY department ORDER BY count DESC LIMIT 5");
        
        return $stats;
    }
    
    // Comments
    public function addComment($procurementId, $userId, $stage, $comment, $isInternal = false) {
        return $this->db->insert('comments', [
            'procurement_id' => $procurementId,
            'user_id' => $userId,
            'stage' => $stage,
            'comment' => $comment,
            'is_internal' => $isInternal
        ]);
    }
    
    public function getComments($procurementId, $stage = null) {
        $sql = "SELECT c.*, u.full_name, u.role 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.procurement_id = :procurement_id";
        $params = ['procurement_id' => $procurementId];
        
        if ($stage) {
            $sql .= " AND c.stage = :stage";
            $params['stage'] = $stage;
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    // Activity log
    private function logActivity($procurementId, $userId, $action, $description = null) {
        $this->db->insert('activity_logs', [
            'procurement_id' => $procurementId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    public function getActivityLog($procurementId) {
        return $this->db->select(
            "SELECT a.*, u.full_name 
             FROM activity_logs a 
             JOIN users u ON a.user_id = u.id 
             WHERE a.procurement_id = :procurement_id 
             ORDER BY a.created_at DESC",
            ['procurement_id' => $procurementId]
        );
    }
    
    // Additional helper method for query execution (if needed)
    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
}