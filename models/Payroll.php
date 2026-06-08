<?php
/**
 * Payroll Model
 */

class Payroll {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $query = "INSERT INTO payroll (employee_id, period_start, period_end, days_worked, day_salary, basic_salary, overtime_hours, overtime_rate, overtime_pay, allowances, deductions, net_salary, payroll_status, payment_method, notes) 
                  VALUES (:employee_id, :period_start, :period_end, :days_worked, :day_salary, :basic_salary, :overtime_hours, :overtime_rate, :overtime_pay, :allowances, :deductions, :net_salary, :payroll_status, :payment_method, :notes)";

        $this->db->prepare($query)
            ->bind(':employee_id', $data['employee_id'], PDO::PARAM_INT)
            ->bind(':period_start', $data['period_start'])
            ->bind(':period_end', $data['period_end'])
            ->bind(':days_worked', $data['days_worked'], PDO::PARAM_INT)
            ->bind(':day_salary', $data['day_salary'])
            ->bind(':basic_salary', $data['basic_salary'])
            ->bind(':overtime_hours', $data['overtime_hours'] ?? 0)
            ->bind(':overtime_rate', $data['overtime_rate'] ?? 0)
            ->bind(':overtime_pay', $data['overtime_pay'] ?? 0)
            ->bind(':allowances', $data['allowances'] ?? 0)
            ->bind(':deductions', $data['deductions'] ?? 0)
            ->bind(':net_salary', $data['net_salary'])
            ->bind(':payroll_status', 'pending')
            ->bind(':payment_method', $data['payment_method'] ?? 'bank_transfer')
            ->bind(':notes', $data['notes'] ?? null)
            ->execute();

        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $query = "SELECT * FROM payroll WHERE id = :id";
        return $this->db->prepare($query)
            ->bind(':id', $id, PDO::PARAM_INT)
            ->single();
    }

    public function getByEmployeeId($employeeId, $limit = null, $offset = 0) {
        $query = "SELECT * FROM payroll WHERE employee_id = :employee_id ORDER BY period_start DESC";

        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $db = $this->db->prepare($query)->bind(':employee_id', $employeeId, PDO::PARAM_INT);

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
            if (in_array($key, ['days_worked', 'basic_salary', 'overtime_hours', 'overtime_pay', 'allowances', 'deductions', 'net_salary', 'payroll_status', 'payment_date', 'payment_method', 'notes'])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE payroll SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM payroll";
        $result = $this->db->prepare($query)->single();
        return $result['total'] ?? 0;
    }
}
?>