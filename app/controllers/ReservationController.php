<?php
// ReservationController
class ReservationController extends Controller {

    private Reservation $model;
    private Approval    $approvalModel;
    private Vehicle     $vehicleModel;
    private Driver      $driverModel;

    public function __construct() {
        $this->model         = new Reservation();
        $this->approvalModel = new Approval();
        $this->vehicleModel  = new Vehicle();
        $this->driverModel   = new Driver();
    }



    public function adminIndex(): void {
        $this->requireRole('admin');
        $this->view('admin.reservations', [
            'reservations' => $this->model->all(),
            'flash'        => $this->getFlash('success'),
        ]);
    }

    public function adminView(): void {
        $this->requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $reservation = $this->model->findById($id);
        if (!$reservation) { http_response_code(404); die('Not found.'); }

        $this->view('admin.reservation_view', [
            'reservation' => $reservation,
            'approvals'   => $this->approvalModel->forReservation($id),
            'vehicles'    => $this->vehicleModel->available(),
            'drivers'     => $this->driverModel->available(),
            'flash'       => $this->getFlash('success'),
        ]);
    }

    /** Assign a vehicle and driver to an approved reservation (Admin). */
    public function assign(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $id        = (int)($_POST['reservation_id'] ?? 0);
        $vehicleId = (int)($_POST['vehicle_id']     ?? 0);
        $driverId  = (int)($_POST['driver_id']      ?? 0);

        if ($vehicleId < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please select a vehicle.'], 422);
            }
            $this->flash('error', 'Please select a vehicle.');
            $this->redirect("admin/reservations/view?id={$id}");
        }

        if ($driverId < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please select a driver.'], 422);
            }
            $this->flash('error', 'Please select a driver.');
            $this->redirect("admin/reservations/view?id={$id}");
        }

        $reservation = $this->model->findById($id);
        if (!$reservation) { http_response_code(404); die('Not found.'); }
        if ($reservation['status'] !== 'approved') {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Reservation must be approved before assignment.'], 422);
            }
            $this->flash('error', 'Reservation must be approved before assignment.');
            $this->redirect("admin/reservations/view?id={$id}");
        }


        $this->model->assignVehicle($id, $vehicleId);
        $this->vehicleModel->setStatus($vehicleId, 'in_use');


        $logModel = new DispatchLog();
        $logModel->create([
            'reservation_id' => $id,
            'driver_id'      => $driverId,
            'vehicle_id'     => $vehicleId,
            'start_mileage'  => 0,
        ]);


        $this->driverModel->setAvailability($driverId, false);


        if ($this->isAjax()) {
            $this->json([
                'success'  => true,
                'message'  => 'Vehicle and driver assigned successfully.',
                'redirect' => BASE_URL . "admin/reservations/view?id={$id}",
            ]);
        }
        $this->flash('success', 'Vehicle and driver assigned successfully.');
        $this->redirect("admin/reservations/view?id={$id}");
    }



    public function pendingApprovals(): void {
        $this->requireRole('staff');
        $reservations = $this->model->pending();

        $this->view('admin.approvals', [
            'reservations' => $reservations,
            'flash'        => $this->getFlash('success'),
        ]);
    }

    public function decide(): void {
        $this->requireRole('staff');
        $this->verifyCsrf();

        $id       = (int)($_POST['reservation_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $remarks  = trim($_POST['remarks'] ?? '');
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid decision.'], 422);
            }
            $this->redirect('approvals');
        }

        $newStatus = $decision === 'approved' ? 'approved' : 'rejected';

        $this->model->updateStatus($id, $newStatus, $remarks ?: null);

        $this->approvalModel->create([
            'reservation_id' => $id,
            'approved_by'    => (int)$_SESSION['user_id'],
            'approval_level' => 'staff',
            'decision'       => $decision,
            'remarks'        => $remarks ?: null,
        ]);

        $msg = $decision === 'approved' ? 'Request approved successfully.' : 'Request has been rejected.';

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => $msg]);
        }
        $this->flash('success', 'Decision recorded successfully.');
        $this->redirect('approvals');
    }



    public function newForm(): void {
        $this->requireRole('requester');
        $this->view('requester.new_request', [
            'flash' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
        ]);
    }

    public function store(): void {
        $this->requireRole('requester');
        $this->verifyCsrf();

        $data = [
            'requester_id'   => (int)$_SESSION['user_id'],
            'purpose'        => $this->postInput('purpose'),
            'destination'    => $this->postInput('destination'),
            'passengers'     => (int)($_POST['passengers']    ?? 1),
            'departure_date' => $_POST['departure_date']      ?? '',
            'departure_time' => $_POST['departure_time']      ?? '',
            'return_date'    => $_POST['return_date']         ?? '',
            'return_time'    => $_POST['return_time']         ?? '',
        ];


        foreach (['purpose','destination','departure_date','departure_time','return_date','return_time'] as $field) {
            if (empty($data[$field])) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Please fill in all required fields.'], 422);
                }
                $this->flash('error', 'Please fill in all required fields.');
                $this->redirect('requester/new');
            }
        }


        if ($data['departure_date'] < date('Y-m-d')) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Departure date cannot be in the past.'], 422);
            }
            $this->flash('error', 'Departure date cannot be in the past.');
            $this->redirect('requester/new');
        }

        if ($data['return_date'] < $data['departure_date']) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Return date must be on or after departure date.'], 422);
            }
            $this->flash('error', 'Return date must be on or after departure date.');
            $this->redirect('requester/new');
        }

        $this->model->create($data);

        if ($this->isAjax()) {
            $this->json([
                'success'  => true,
                'message'  => 'Reservation submitted successfully!',
                'redirect' => BASE_URL . 'requester/my_requests',
            ]);
        }
        $this->flash('success', 'Reservation submitted successfully!');
        $this->redirect('requester/my_requests');
    }

    public function myRequests(): void {
        $this->requireRole('requester');
        $this->view('requester.my_requests', [
            'requests' => $this->model->byRequester((int)$_SESSION['user_id']),
            'flash'    => $this->getFlash('success'),
        ]);
    }

    public function cancel(): void {
        $this->requireRole('requester');
        $this->verifyCsrf();

        $id = (int)($_POST['reservation_id'] ?? 0);
        $this->model->cancel($id, (int)$_SESSION['user_id']);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Reservation cancelled.']);
        }
        $this->flash('success', 'Reservation cancelled.');
        $this->redirect('requester/my_requests');
    }
}
