<?php
$pageTitle = 'Driver Dashboard';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Driver Dashboard</h1>
                <p>Hello, <?= htmlspecialchars($_SESSION['full_name']) ?>. Here are your assigned trips.</p>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <?php if (empty($trips)): ?>
            <div class="panel active">
                <p class="empty-row">No trips currently assigned to you.</p>
            </div>
        <?php else: ?>
            <?php foreach ($trips as $t): ?>
            <section class="panel active trip-card" id="trip-card-<?= (int)$t['reservation_id'] ?>">
                <div class="trip-header">
                    <span class="trip-ref"><?= htmlspecialchars($t['reference_no']) ?></span>
                    <span class="badge badge-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span>
                </div>
                <div class="trip-details">
                    <div><strong>Destination:</strong> <?= htmlspecialchars($t['destination']) ?></div>
                    <div><strong>Departure:</strong> <?= htmlspecialchars($t['departure_date']) ?></div>
                    <div><strong>Vehicle:</strong> <?= htmlspecialchars($t['make_model'] ?? '—') ?> (<?= htmlspecialchars($t['plate_number'] ?? '—') ?>)</div>
                </div>

                <?php if ($t['status'] === 'approved'): ?>
                    <form method="POST" action="<?= BASE_URL ?>driver/dispatch"
                          data-ajax-url="<?= BASE_URL ?>api/driver/dispatch"
                          class="ajax-driver-form">
                        <?= Controller::csrfField() ?>
                        <input type="hidden" name="reservation_id" value="<?= (int)$t['reservation_id'] ?>"/>
                        <div class="form-group inline">
                            <label for="start_<?= $t['reservation_id'] ?>">Start Mileage (km)</label>
                            <input type="number" id="start_<?= $t['reservation_id'] ?>"
                                   name="start_mileage" min="0" step="0.1" required/>
                        </div>
                        <button type="submit" class="btn-primary">Start Trip</button>
                    </form>

                <?php elseif ($t['status'] === 'dispatched'): ?>
                    <form method="POST" action="<?= BASE_URL ?>driver/complete"
                          data-ajax-url="<?= BASE_URL ?>api/driver/complete"
                          class="ajax-driver-form">
                        <?= Controller::csrfField() ?>
                        <input type="hidden" name="reservation_id" value="<?= (int)$t['reservation_id'] ?>"/>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="end_<?= $t['reservation_id'] ?>">End Mileage (km)</label>
                                <input type="number" id="end_<?= $t['reservation_id'] ?>"
                                       name="end_mileage" min="0" step="0.1" required/>
                            </div>
                            <div class="form-group">
                                <label>Fuel Consumed (L)</label>
                                <input type="number" name="fuel_consumed" min="0" step="0.01"/>
                            </div>
                            <div class="form-group full-width">
                                <label>Trip Notes</label>
                                <textarea name="trip_notes" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn-success">Complete Trip</button>
                    </form>
                <?php endif; ?>
            </section>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script src="<?= BASE_URL ?>public/js/dashboard.js"></script>
<script>

    document.querySelectorAll('.ajax-driver-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const result = await VRS.ajax.submitForm(form, {
                submitBtn: btn,
                onSuccess: (data) => {
                    VRS.notify.success(data.message);

                    setTimeout(() => window.location.reload(), 1200);
                },
            });
        });
    });
</script>
