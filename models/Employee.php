<?php
/**
 * Employee Model
 */

class Employee {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $query = "INSERT INTO employees (full_name, email, phone, position, site_id, hire_date, day_salary, employment_type, status) 
                  VALUES (:full_name, :email, :phone, :position, :site_id, :hire_date, :day_salary, :employment_type, :status)";

        $this->db->prepare($query)
            ->bind(':full_name', $data['full_name'])
            ->bind(':email', $data['email'])
            ->bind(':phone', $data['phone'])
            ->bind(':position', $data['position'])
            ->bind(':site_id', $data['site_id'] ?? null, PDO::PARAM_INT)
            ->bind(':hire_date', $data['hire_date'])
            ->bind(':day_salary', $data['day_salary'])
            ->bind(':employment_type', $data['employment_type'] ?? 'full_time')
            ->bind(':status', 'active')
            ->execute();

        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $query = "SELECT * FROM employees WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->single();
    }

    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM employees ORDER BY created_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $db = $this->db->prepare($query);

        if ($limit !== null) {
            $db->bind(':limit', $limit, PDO::PARAM_INT)
               ->bind(':offset', $offset, PDO::PARAM_INT);
        }

        return $db->all();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['full_name', 'email', 'phone', 'position', 'site_id', 'day_salary', 'status', 'profile_photo'])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE employees SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM employees WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->execute();
    }

    public function getByStatus($status, $limit = null, $offset = 0) {
        $query = "SELECT * FROM employees WHERE status = :status ORDER BY created_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $db = $this->db->prepare($query)->bind(':status', $status);

        if ($limit !== null) {
            $db->bind(':limit', $limit, PDO::PARAM_INT)
               ->bind(':offset', $offset, PDO::PARAM_INT);
        }

        return $db->all();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM employees";
        $result = $this->db->prepare($query)->single();
        return $result['total'] ?? 0;
    }

    public function countByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM employees WHERE status = :status";
        $result = $this->db->prepare($query)->bind(':status', $status)->single();
        return $result['total'] ?? 0;
    }
}
?>