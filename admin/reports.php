<?php
require_once 'config.php';
require_role('admin');

// Get system statistics
$stats = [];

// User statistics
$stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stmt->execute();
while ($row = $stmt->fetch()) {
    $stats['users'][$row['role']] = $row['count'];
}

// Appointment statistics
$stmt = $pdo->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        DATE(appointment_date) as date
    FROM appointments 
    WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY status, DATE(appointment_date)
    ORDER BY date DESC
");
$stmt->execute();
$appointment_stats = $stmt->fetchAll();

// Revenue calculation (if consultation fees are paid)
$stmt = $pdo->prepare("
    SELECT 
        SUM(dp.consultation_fee) as total_revenue,
        COUNT(*) as completed_appointments
    FROM appointments a
    JOIN doctor_profiles dp ON a.doctor_id = dp.user_id
    WHERE a.status = 'completed'
    AND a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
");
$stmt->execute();
$revenue = $stmt->fetch();

// Top doctors by appointments
$stmt = $pdo->prepare("
    SELECT u.name, COUNT(a.id) as appointment_count
    FROM users u
    JOIN appointments a ON u.id = a.doctor_id
    WHERE u.role = 'doctor'
    GROUP BY u.id
    ORDER BY appointment_count DESC
    LIMIT 5
");
$stmt->execute();
$top_doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-heartbeat me-2"></i>MediCare
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2><i class="fas fa-chart-bar me-2"></i>System Reports</h2>

        <div class="row mb-4">
            <!-- User Statistics -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">User Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Patients:</strong> <?= $stats['users']['patient'] ?? 0 ?>
                        </div>
                        <div class="mb-2">
                            <strong>Doctors:</strong> <?= $stats['users']['doctor'] ?? 0 ?>
                        </div>
                        <div class="mb-2">
                            <strong>Admins:</strong> <?= $stats['users']['admin'] ?? 0 ?>
                        </div>
                        <hr>
                        <div class="fw-bold">
                            Total Users: <?= array_sum($stats['users'] ?? []) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Statistics -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Revenue (Last 30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Total Revenue:</strong><br>
                            <span class="h4 text-success">₹<?= number_format($revenue['total_revenue'] ?? 0) ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Completed Appointments:</strong> <?= $revenue['completed_appointments'] ?? 0 ?>
                        </div>
                        <div class="mb-2">
                            <strong>Average per Appointment:</strong><br>
                            ₹<?= $revenue['completed_appointments'] > 0 ? number_format(($revenue['total_revenue'] ?? 0) / $revenue['completed_appointments']) : 0 ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Doctors -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top Doctors</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($top_doctors)): ?>
                            <p class="text-muted">No data available</p>
                        <?php else: ?>
                            <?php foreach ($top_doctors as $doctor): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Dr. <?= sanitize($doctor['name']) ?></span>
                                    <span class="badge bg-primary"><?= $doctor['appointment_count'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Status Chart -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Appointment Status (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <?php
                $status_counts = [];
                foreach ($appointment_stats as $stat) {
                    $status_counts[$stat['status']] = ($status_counts[$stat['status']] ?? 0) + $stat['count'];
                }
                ?>
                
                <?php if (empty($status_counts)): ?>
                    <p class="text-muted">No appointments in the last 30 days</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($status_counts as $status => $count): ?>
                            <div class="col-md-3 mb-3">
                                <div class="text-center">
                                    <div class="h2 text-<?= $status === 'completed' ? 'success' : ($status === 'scheduled' ? 'primary' : 'secondary') ?>">
                                        <?= $count ?>
                                    </div>
                                    <div class="text-muted"><?= ucfirst($status) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>