<?php
// ─── VehicleController ───────────────────────────────────────────────────────
class VehicleController extends Controller {

    private Vehicle $model;

    public function __construct() {
        $this->model = new Vehicle();
    }

    public function index(): void {
        $this->requireRole('admin', 'asd_coordinator');
        $this->view('admin.vehicles', [
            'vehicles' => $this->model->all(),
            'flash'    => $this->getFlash('success'),
        ]);
    }

    public function create(): void {
        $this->requireRole('admin', 'asd_coordinator');
        $this->view('admin.vehicle_form', [
            'vehicle' => null,
            'error'   => $this->getFlash('error'),
        ]);
    }

    public function store(): void {
        $this->requireRole('admin', 'asd_coordinator');
        $this->verifyCsrf();

        $data = [
            'plate_number' => $this->postInput('plate_number'),
            'make_model'   => $this->postInput('make_model'),
            'vehicle_type' => $this->postInput('vehicle_type'),
            'capacity'     => (int)($_POST['capacity'] ?? 0),
            'year'         => $_POST['year']  ?? null,
            'color'        => $this->postInput('color'),
            'notes'        => $this->postInput('notes'),
        ];

        if (empty($data['plate_number']) || empty($data['make_model']) || $data['capacity'] < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Plate number, model, and capacity are required.'], 422);
            }
            $this->flash('error', 'Plate number, model, and capacity are required.');
            $this->redirect('admin/vehicles/create');
        }

        try {
            $this->model->create($data);
        } catch (\PDOException $e) {
            $msg = 'Failed to add vehicle.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $msg = 'A vehicle with this plate number already exists.';
            }
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 422);
            }
            $this->flash('error', $msg);
            $this->redirect('admin/vehicles/create');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Vehicle added successfully.', 'redirect' => BASE_URL . 'admin/vehicles']);
        }
        $this->flash('success', 'Vehicle added successfully.');
        $this->redirect('admin/vehicles');
    }

    public function edit(): void {
        $this->requireRole('admin', 'asd_coordinator');
        $id = (int)($_GET['id'] ?? 0);
        $vehicle = $this->model->findById($id);
        if (!$vehicle) { http_response_code(404); die('Vehicle not found.'); }
        $this->view('admin.vehicle_form', ['vehicle' => $vehicle, 'error' => null]);
    }

    public function update(): void {
        $this->requireRole('admin', 'asd_coordinator');
        $this->verifyCsrf();

        $id   = (int)($_POST['vehicle_id'] ?? 0);
        $data = [
            'plate_number' => $this->postInput('plate_number'),
            'make_model'   => $this->postInput('make_model'),
            'vehicle_type' => $this->postInput('vehicle_type'),
            'capacity'     => (int)($_POST['capacity'] ?? 0),
            'year'         => $_POST['year']   ?? null,
            'color'        => $this->postInput('color'),
            'status'       => $_POST['status'] ?? 'available',
            'notes'        => $this->postInput('notes'),
        ];

        // Validate status
        $validStatuses = ['available', 'in_use', 'maintenance', 'retired'];
        if (!in_array($data['status'], $validStatuses, true)) {
            $data['status'] = 'available';
        }

        try {
            $this->model->update($id, $data);
        } catch (\PDOException $e) {
            $msg = 'Failed to update vehicle.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 422);
            }
            $this->flash('error', $msg);
            $this->redirect('admin/vehicles');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Vehicle updated successfully.', 'redirect' => BASE_URL . 'admin/vehicles']);
        }
        $this->flash('success', 'Vehicle updated successfully.');
        $this->redirect('admin/vehicles');
    }
}
