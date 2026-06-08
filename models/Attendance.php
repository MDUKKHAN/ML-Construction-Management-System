<?php
/**
 * Attendance Model
 */

class Attendance {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $query = "INSERT INTO attendance (employee_id, attendance_date, status, site_id, check_in, check_out, working_hours, overtime_hours, notes, marked_by) 
                  VALUES (:employee_id, :attendance_date, :status, :site_id, :check_in, :check_out, :working_hours, :overtime_hours, :notes, :marked_by)";

        $this->db->prepare($query)
            ->bind(':employee_id', $data['employee_id'], PDO::PARAM_INT)
            ->bind(':attendance_date', $data['attendance_date'])
            ->bind(':status', $data['status'] ?? 'absent')
            ->bind(':site_id', $data['site_id'] ?? null, PDO::PARAM_INT)
            ->bind(':check_in', $data['check_in'] ?? null)
            ->bind(':check_out', $data['check_out'] ?? null)
            ->bind(':working_hours', $data['working_hours'] ?? 0)
            ->bind(':overtime_hours', $data['overtime_hours'] ?? 0)
            ->bind(':notes', $data['notes'] ?? null)
            ->bind(':marked_by', $data['marked_by'] ?? null, PDO::PARAM_INT)
            ->execute();

        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $query = "SELECT * FROM attendance WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->single();
    }

    public function getByEmployeeAndDate($employeeId, $date) {
        $query = "SELECT * FROM attendance WHERE employee_id = :employee_id AND attendance_date = :date";
        return $this->db->prepare($query)
            ->bind(':employee_id', $employeeId, PDO::PARAM_INT)
            ->bind(':date', $date)
            ->single();
    }

    public function getByEmployeeMonth($employeeId, $month, $year) {
        $query = "SELECT * FROM attendance WHERE employee_id = :employee_id AND MONTH(attendance_date) = :month AND YEAR(attendance_date) = :year ORDER BY attendance_date ASC";
        return $this->db->prepare($query)
            ->bind(':employee_id', $employeeId, PDO::PARAM_INT)
            ->bind(':month', $month, PDO::PARAM_INT)
            ->bind(':year', $year, PDO::PARAM_INT)
            ->all();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['status', 'site_id', 'check_in', 'check_out', 'working_hours', 'overtime_hours', 'notes'])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE attendance SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM attendance WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->execute();
    }
}
?>