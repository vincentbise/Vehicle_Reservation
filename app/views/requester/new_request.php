<?php
$pageTitle = 'New Reservation';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>New Reservation Request</h1>
                <p>Fill in the details below to request a university vehicle.</p>
            </div>
        </section>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <section class="panel active form-panel">
            <form method="POST" action="<?= BASE_URL ?>requester/store"
                  data-ajax-url="<?= BASE_URL ?>api/reservations/store"
                  id="reservation-form" novalidate>
                <?= Controller::csrfField() ?>

                <div class="form-grid">

                    <div class="form-group full-width">
                        <label for="purpose">Purpose / Reason for Travel <span class="required">*</span></label>
                        <textarea id="purpose" name="purpose" rows="3"
                                  placeholder="Briefly describe the purpose of this trip..."
                                  required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="destination">Destination <span class="required">*</span></label>
                        <input type="text" id="destination" name="destination"
                               placeholder="e.g., USeP Obrero Campus, Tagum City" required/>
                    </div>

                    <div class="form-group">
                        <label for="passengers">Number of Passengers <span class="required">*</span></label>
                        <input type="number" id="passengers" name="passengers"
                               min="1" max="50" value="1" required/>
                    </div>

                    <div class="form-group">
                        <label for="departure_date">Departure Date <span class="required">*</span></label>
                        <input type="date" id="departure_date" name="departure_date" required/>
                    </div>

                    <div class="form-group">
                        <label for="departure_time">Departure Time <span class="required">*</span></label>
                        <input type="time" id="departure_time" name="departure_time" required/>
                    </div>

                    <div class="form-group">
                        <label for="return_date">Return Date <span class="required">*</span></label>
                        <input type="date" id="return_date" name="return_date" required/>
                    </div>

                    <div class="form-group">
                        <label for="return_time">Return Time <span class="required">*</span></label>
                        <input type="time" id="return_time" name="return_time" required/>
                    </div>

                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>requester/my_requests" class="btn-outline">Cancel</a>
                    <button type="submit" class="btn-primary" id="submit-btn">Submit Request</button>
                </div>

            </form>
        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script src="<?= BASE_URL ?>public/js/reservation.js"></script>
