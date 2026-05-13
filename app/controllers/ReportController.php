<?php
// ReportController
class ReportController extends Controller {

    public function index(): void {
        $this->requireRole('admin', 'asd_coordinator');

        $db   = Database::getInstance();
        $type = $_GET['type'] ?? 'daily';

        $data = match ($type) {
            'daily'       => $this->daily($db),
            'utilization' => $this->utilization($db),
            'monthly'     => $this->monthly($db),
            'drivers'     => $this->driverSummary($db),
            default       => [],
        };

        $this->view('admin.reports', [
            'type'  => $type,
            'data'  => $data,
            'flash' => $this->getFlash('success'),
        ]);
    }

    private function daily(PDO $db): array {
        $date = $_GET['date'] ?? date('Y-m-d');
        $stmt = $db->prepare(
            'SELECT r.reference_no, u.full_name AS requester, r.destination,
                    r.departure_date, r.return_date, r.status,
                    v.make_model, v.plate_number
             FROM   reservations r
             JOIN   users u ON u.user_id = r.requester_id
             LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE  r.departure_date = ?
             ORDER  BY r.departure_date ASC'
        );
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    private function utilization(PDO $db): array {
        $stmt = $db->query(
            'SELECT v.make_model, v.plate_number, v.status,
                    COUNT(dl.log_id) AS trips_completed,
                    SUM(dl.end_mileage - dl.start_mileage) AS total_km,
                    SUM(dl.fuel_consumed) AS total_fuel
             FROM   vehicles v
             LEFT JOIN dispatch_logs dl ON dl.vehicle_id = v.vehicle_id
             GROUP  BY v.vehicle_id
             ORDER  BY trips_completed DESC'
        );
        return $stmt->fetchAll();
    }

    private function monthly(PDO $db): array {
        $stmt = $db->query(
            'SELECT DATE_FORMAT(departure_date, "%Y-%m") AS month,
                    COUNT(*) AS total,
                    SUM(status = "completed")  AS completed,
                    SUM(status = "rejected")   AS rejected,
                    SUM(status = "cancelled")  AS cancelled
             FROM   reservations
             GROUP  BY month
             ORDER  BY month DESC
             LIMIT  12'
        );
        return $stmt->fetchAll();
    }

    private function driverSummary(PDO $db): array {
        $stmt = $db->query(
            'SELECT u.full_name AS driver, d.license_no,
                    COUNT(dl.log_id) AS trips,
                    SUM(dl.end_mileage - dl.start_mileage) AS total_km
             FROM   drivers d
             JOIN   users u ON u.user_id = d.user_id
             LEFT JOIN dispatch_logs dl ON dl.driver_id = d.driver_id
             GROUP  BY d.driver_id
             ORDER  BY trips DESC'
        );
        return $stmt->fetchAll();
    }
}
