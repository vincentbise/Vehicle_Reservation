<?php
// ─── Vehicle Model ───────────────────────────────────────────────────────────
class Vehicle extends Model {

    public function all(): array {
        return $this->query('SELECT * FROM vehicles ORDER BY make_model');
    }

    /** Available vehicles for assignment. */
    public function available(): array {
        return $this->query(
            'SELECT * FROM vehicles WHERE status = ? ORDER BY make_model',
            ['available']
        );
    }

    public function findById(int $id): ?array {
        return $this->queryOne('SELECT * FROM vehicles WHERE vehicle_id = ?', [$id]);
    }

    public function create(array $data): void {
        $this->execute(
            'INSERT INTO vehicles (plate_number, make_model, vehicle_type, capacity, year, color, notes)
             VALUES (?,?,?,?,?,?,?)',
            [
                $data['plate_number'],
                $data['make_model'],
                $data['vehicle_type'] ?? null,
                $data['capacity']     ?? 1,
                $data['year']         ?? null,
                $data['color']        ?? null,
                $data['notes']        ?? null,
            ]
        );
    }

    public function update(int $id, array $data): void {
        $this->execute(
            'UPDATE vehicles
             SET plate_number=?, make_model=?, vehicle_type=?,
                 capacity=?, year=?, color=?, status=?, notes=?
             WHERE vehicle_id=?',
            [
                $data['plate_number'],
                $data['make_model'],
                $data['vehicle_type'] ?? null,
                $data['capacity']     ?? 1,
                $data['year']         ?? null,
                $data['color']        ?? null,
                $data['status']       ?? 'available',
                $data['notes']        ?? null,
                $id,
            ]
        );
    }

    public function setStatus(int $id, string $status): void {
        $this->execute(
            'UPDATE vehicles SET status = ? WHERE vehicle_id = ?',
            [$status, $id]
        );
    }

    public function countByStatus(string $status): int {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS n FROM vehicles WHERE status = ?', [$status]
        );
        return (int)($row['n'] ?? 0);
    }

    public function countAll(): int {
        $row = $this->queryOne('SELECT COUNT(*) AS n FROM vehicles');
        return (int)($row['n'] ?? 0);
    }
}
