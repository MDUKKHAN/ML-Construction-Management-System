<?php
/**
 * Activity Model for logging
 */

class Activity {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function log($userId, $action, $module, $recordId = null, $description = null) {
        $query = "INSERT INTO activities (user_id, action, module, record_id, description, ip_address, user_agent) 
                  VALUES (:user_id, :action, :module, :record_id, :description, :ip_address, :user_agent)";

        $this->db->prepare($query)
            ->bind(':user_id', $userId, PDO::PARAM_INT)
            ->bind(':action', $action)
            ->bind(':module', $module)
            ->bind(':record_id', $recordId, PDO::PARAM_INT)
            ->bind(':description', $description)
            ->bind(':ip_address', $_SERVER['REMOTE_ADDR'] ?? 'Unknown')
            ->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown')
            ->execute();

        return $this->db->lastInsertId();
    }

    public function getRecent($limit = 10) {
        $query = "SELECT * FROM activities ORDER BY created_at DESC LIMIT :limit";
        return $this->db->prepare($query)
            ->bind(':limit', $limit, PDO::PARAM_INT)
            ->all();
    }

    public function getByUser($userId, $limit = 10) {
        $query = "SELECT * FROM activities WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
        return $this->db->prepare($query)
            ->bind(':user_id', $userId, PDO::PARAM_INT)
            ->bind(':limit', $limit, PDO::PARAM_INT)
            ->all();
    }
}
?>