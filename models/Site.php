<?php
/**
 * Site Model
 */

class Site {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $query = "INSERT INTO sites (site_name, site_address, gps_location, google_map_link, site_manager_id, site_status, start_date, end_date, budget, notes) 
                  VALUES (:site_name, :site_address, :gps_location, :google_map_link, :site_manager_id, :site_status, :start_date, :end_date, :budget, :notes)";

        $this->db->prepare($query)
            ->bind(':site_name', $data['site_name'])
            ->bind(':site_address', $data['site_address'])
            ->bind(':gps_location', $data['gps_location'] ?? null)
            ->bind(':google_map_link', $data['google_map_link'] ?? null)
            ->bind(':site_manager_id', $data['site_manager_id'] ?? null, PDO::PARAM_INT)
            ->bind(':site_status', 'active')
            ->bind(':start_date', $data['start_date'] ?? null)
            ->bind(':end_date', $data['end_date'] ?? null)
            ->bind(':budget', $data['budget'] ?? 0)
            ->bind(':notes', $data['notes'] ?? null)
            ->execute();

        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $query = "SELECT * FROM sites WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->single();
    }

    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM sites ORDER BY created_at DESC";

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
            if (in_array($key, ['site_name', 'site_address', 'gps_location', 'google_map_link', 'site_manager_id', 'site_status', 'start_date', 'end_date', 'budget', 'notes'])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE sites SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM sites WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM sites";
        $result = $this->db->prepare($query)->single();
        return $result['total'] ?? 0;
    }
}
?>