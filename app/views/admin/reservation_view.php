<?php
$pageTitle = 'Reservation Detail – ' . htmlspecialchars($reservation['reference_no']);
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1><?= htmlspecialchars($reservation['reference_no']) ?></h1>
                <p>Full reservation details and approval history.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/reservations" class="btn-outline">← Back</a>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Details Card -->
        <section class="panel active detail-grid">
            <div class="detail-section">
                <h3>Request Details</h3>
                <dl class="detail-list">
                    <div><dt>Reference No.</dt><dd><?= htmlspecialchars($reservation['reference_no']) ?></dd></div>
                    <div><dt>Status</dt>
                         <dd><span class="badge badge-<?= $reservation['status'] ?>">
                             <?= ucwords(str_replace('_',' ',$reservation['status'])) ?>
                         </span></dd></div>
                    <div><dt>Requester</dt>   <dd><?= htmlspecialchars($reservation['requester_name']) ?></dd></div>
                    <div><dt>Department</dt>  <dd><?= htmlspecialchars($reservation['department'] ?? '—') ?></dd></div>
                    <div><dt>Contact</dt>     <dd><?= htmlspecialchars($reservation['contact_no']  ?? '—') ?></dd></div>
                    <div><dt>Destination</dt> <dd><?= htmlspecialchars($reservation['destination']) ?></dd></div>
                    <div><dt>Passengers</dt>  <dd><?= (int)$reservation['passengers'] ?></dd></div>
                    <div><dt>Purpose</dt>     <dd><?= nl2br(htmlspecialchars($reservation['purpose'])) ?></dd></div>
                    <div><dt>Departure</dt>   <dd><?= htmlspecialchars($reservation['departure_date'] . ' ' . $reservation['departure_time']) ?></dd></div>
                    <div><dt>Return</dt>      <dd><?= htmlspecialchars($reservation['return_date']   . ' ' . $reservation['return_time'])   ?></dd></div>
                    <?php if ($reservation['remarks']): ?>
                    <div class="full-detail"><dt>Remarks</dt><dd><?= nl2br(htmlspecialchars($reservation['remarks'])) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Assign Vehicle & Driver (ASD only, when unit_approved) -->
            <?php if ($reservation['status'] === 'unit_approved'
                   && in_array($_SESSION['role'], ['admin','asd_coordinator'], true)): ?>
            <div class="detail-section">
                <h3>Assign Vehicle & Driver</h3>
                <form method="POST" action="<?= BASE_URL ?>admin/reservations/assign"
                      data-ajax-url="<?= BASE_URL ?>api/reservations/assign"
                      id="assign-form">
                    <?= Controller::csrfField() ?>
                    <input type="hidden" name="reservation_id" value="<?= (int)$reservation['reservation_id'] ?>"/>

                    <div class="form-group">
                        <label for="vehicle_id">Select Vehicle <span class="required">*</span></label>
                        <select id="vehicle_id" name="vehicle_id" required>
                            <option value="">— Choose a vehicle —</option>
                            <?php if (!empty($vehicles)): ?>
                            <?php foreach ($vehicles as $v): ?>
                            <option value="<?= (int)$v['vehicle_id'] ?>">
                                <?= htmlspecialchars($v['make_model'] . ' (' . $v['plate_number'] . ') – ' . $v['capacity'] . ' pax') ?>
                            </option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="driver_id">Assign Driver <span class="required">*</span></label>
                        <select id="driver_id" name="driver_id" required>
                            <option value="">— Choose a driver —</option>
                            <?php if (!empty($drivers)): ?>
                            <?php foreach ($drivers as $d): ?>
                            <option value="<?= (int)$d['driver_id'] ?>">
                                <?= htmlspecialchars($d['full_name'] . ' (License: ' . $d['license_no'] . ')') ?>
                            </option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">Assign & Approve</button>
                </form>
            </div>
            <?php endif; ?>
        </section>

        <!-- Approval History -->
        <?php if (!empty($approvals)): ?>
        <section class="panel active">
            <h3>Approval History</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Approver</th>
                            <th>Decision</th>
                            <th>Remarks</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($approvals as $a): ?>
                        <tr>
                            <td><?= ucwords(str_replace('_',' ',$a['approval_level'])) ?></td>
                            <td><?= htmlspecialchars($a['approver_name']) ?></td>
                            <td>
                                <span class="badge badge-<?= $a['decision'] === 'approved' ? 'available' : 'rejected' ?>">
                                    <?= ucfirst($a['decision']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($a['remarks'] ?? '—') ?></td>
                            <td><?= date('M d, Y g:i A', strtotime($a['decided_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script>
    const assignForm = document.getElementById('assign-form');
    if (assignForm) {
        assignForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await VRS.ajax.submitForm(assignForm, {
                onSuccess: (data) => {
                    VRS.notify.success(data.message);
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 1000);
                    }
                },
            });
        });
    }
</script>
