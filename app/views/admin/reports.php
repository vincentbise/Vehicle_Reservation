<?php
$pageTitle = 'Reports';
include VIEW_PATH . '/layouts/header.php';

$types = [
    'daily'       => 'Daily Reservation Report',
    'utilization' => 'Vehicle Utilization Report',
    'monthly'     => 'Monthly Request Trends',
    'drivers'     => 'Driver Assignment Summary',
];
$currentType  = $type ?? 'daily';
$currentLabel = $types[$currentType] ?? 'Report';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Reports</h1>
                <p>Generate and export operational reports for the USeP Vehicle Reservation System.</p>
            </div>
        </section>

        <!-- Report type selector -->
        <section class="panel active">
            <div class="report-grid">
                <?php foreach ($types as $key => $label): ?>
                <a href="<?= BASE_URL ?>admin/reports?type=<?= $key ?>"
                   class="report-card <?= $currentType === $key ? 'report-card--active' : '' ?>">
                    <span><?= $label ?></span>
                    <span class="btn-sm">View</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Report Output -->
        <section class="panel active">
            <div class="panel-header">
                <h3><?= $currentLabel ?></h3>
                <?php if ($currentType === 'daily'): ?>
                    <form method="GET" action="<?= BASE_URL ?>admin/reports" class="date-filter">
                        <input type="hidden" name="type" value="daily"/>
                        <input type="date" name="date"
                               value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d')) ?>"/>
                        <button type="submit" class="btn-sm">Filter</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (empty($data)): ?>
                <p class="empty-row">No data available for this report.</p>

            <?php elseif ($currentType === 'daily'): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Ref No.</th><th>Requester</th><th>Destination</th>
                            <th>Departure</th><th>Return</th><th>Vehicle</th><th>Status</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['reference_no']) ?></td>
                            <td><?= htmlspecialchars($r['requester']) ?></td>
                            <td><?= htmlspecialchars($r['destination']) ?></td>
                            <td><?= htmlspecialchars($r['departure_date']) ?></td>
                            <td><?= htmlspecialchars($r['return_date']) ?></td>
                            <td><?= htmlspecialchars($r['make_model'] ?? '—') ?></td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucwords(str_replace('_',' ',$r['status'])) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($currentType === 'utilization'): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Vehicle</th><th>Plate</th><th>Status</th>
                            <th>Trips</th><th>Total KM</th><th>Fuel (L)</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['make_model']) ?></td>
                            <td><?= htmlspecialchars($r['plate_number']) ?></td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucwords(str_replace('_',' ',$r['status'])) ?></span></td>
                            <td><?= (int)$r['trips_completed'] ?></td>
                            <td><?= number_format((float)($r['total_km'] ?? 0), 1) ?> km</td>
                            <td><?= number_format((float)($r['total_fuel'] ?? 0), 2) ?> L</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($currentType === 'monthly'): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Month</th><th>Total</th>
                            <th>Completed</th><th>Rejected</th><th>Cancelled</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['month']) ?></td>
                            <td><strong><?= (int)$r['total'] ?></strong></td>
                            <td><?= (int)$r['completed'] ?></td>
                            <td><?= (int)$r['rejected'] ?></td>
                            <td><?= (int)$r['cancelled'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($currentType === 'drivers'): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Driver</th><th>License No.</th>
                            <th>Trips Completed</th><th>Total KM</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['driver']) ?></td>
                            <td><?= htmlspecialchars($r['license_no']) ?></td>
                            <td><?= (int)$r['trips'] ?></td>
                            <td><?= number_format((float)($r['total_km'] ?? 0), 1) ?> km</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script src="<?= BASE_URL ?>public/js/reports.js"></script>
