<?php
require_once __DIR__ . '/../config/database.php';

class AuditController
{
    private $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            error_log("AuditController init error: " . $e->getMessage());
        }
    }

    /**
     * Log a significant user action in the database
     *
     * @param int|null $userId ID of the active user, or null if system/anonymous
     * @param string $action Description of action (e.g. 'login', 'void_sale')
     * @param string|null $tableAffected Table modified
     * @param int|null $recordId ID of the primary record
     * @param array|null $oldValues Array of previous values
     * @param array|null $newValues Array of updated values
     * @return bool
     */
    public static function logActivity($userId, string $action, string $tableAffected = null, int $recordId = null, array $oldValues = null, array $newValues = null): bool
    {
        try {
            $database = new Database();
            $db = $database->getConnection();
            if (!$db) {
                return false;
            }

            $query = "INSERT INTO audit_log 
                      SET user_id = :user_id,
                          action = :action,
                          table_affected = :table_affected,
                          record_id = :record_id,
                          old_values = :old_values,
                          new_values = :new_values,
                          ip_address = :ip_address,
                          user_agent = :user_agent";

            $stmt = $db->prepare($query);

            $oldJson = $oldValues !== null ? json_encode($oldValues) : null;
            $newJson = $newValues !== null ? json_encode($newValues) : null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':action', $action, PDO::PARAM_STR);
            $stmt->bindParam(':table_affected', $tableAffected, PDO::PARAM_STR);
            $stmt->bindParam(':record_id', $recordId, PDO::PARAM_INT);
            $stmt->bindParam(':old_values', $oldJson, PDO::PARAM_STR);
            $stmt->bindParam(':new_values', $newJson, PDO::PARAM_STR);
            $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve all audit logs for administrator viewing
     *
     * @param array $filters
     * @return array
     */
    public function getLogs(array $filters = []): array
    {
        if (!$this->db) {
            return [];
        }

        $query = "SELECT a.*, u.full_name, u.username 
                  FROM audit_log a
                  LEFT JOIN users u ON a.user_id = u.id
                  WHERE 1=1";
        
        $params = [];

        if (!empty($filters['user_id'])) {
            $query .= " AND a.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $query .= " AND a.action LIKE :action";
            $params[':action'] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['table_affected'])) {
            $query .= " AND a.table_affected = :table_affected";
            $params[':table_affected'] = $filters['table_affected'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND a.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND a.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $query .= " ORDER BY a.created_at DESC";

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to fetch audit logs: " . $e->getMessage());
            return [];
        }
    }
}
