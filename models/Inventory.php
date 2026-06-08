<?php
/**
 * Inventory Model
 */

class Inventory {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $query = "INSERT INTO inventory (product_name, quantity, unit_price, employee_id, site_id, date_received, expiry_date, supplier, remarks) 
                  VALUES (:product_name, :quantity, :unit_price, :employee_id, :site_id, :date_received, :expiry_date, :supplier, :remarks)";

        $this->db->prepare($query)
            ->bind(':product_name', $data['product_name'])
            ->bind(':quantity', $data['quantity'], PDO::PARAM_INT)
            ->bind(':unit_price', $data['unit_price'] ?? 0)
            ->bind(':employee_id', $data['employee_id'] ?? null, PDO::PARAM_INT)
            ->bind(':site_id', $data['site_id'] ?? null, PDO::PARAM_INT)
            ->bind(':date_received', $data['date_received'] ?? null)
            ->bind(':expiry_date', $data['expiry_date'] ?? null)
            ->bind(':supplier', $data['supplier'] ?? null)
            ->bind(':remarks', $data['remarks'] ?? null)
            ->execute();

        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $query = "SELECT * FROM inventory WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->single();
    }

    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM inventory ORDER BY created_at DESC";

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
            if (in_array($key, ['product_name', 'quantity', 'unit_price', 'employee_id', 'site_id', 'supplier', 'remarks'])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE inventory SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM inventory WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM inventory";
        $result = $this->db->prepare($query)->single();
        return $result['total'] ?? 0;
    }
}
?>