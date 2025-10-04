<?php
require_once 'config.php';
require_role('doctor');

// Get doctor's schedule for the week
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+7 days'));

$stmt = $pdo->prepare("
    SELECT a.*, u.name as patient_name, u.phone as patient_phone
    FROM appointments a 
    JOIN users u ON a.patient_id = u.id 
    WHERE a.doctor_id = ? AND a.appointment_date BETWEEN ? AND ?
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
$appointments = $stmt->fetchAll();

// Group appointments by date
$schedule = [];
foreach ($appointments as $appointment) {
    $date = $appointment['appointment_date'];
    if (!isset($schedule[$date])) {
        $schedule[$date] = [];
    }
    $schedule[$date][] = $appointment;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .schedule-day { border-left: 4px solid #667eea; }
        .appointment-slot { background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; }
    </style>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-clock me-2"></i>My Schedule</h2>
                <p class="text-muted">Your appointments for the next 7 days</p>
            </div>
            <div>
                <span class="badge bg-primary fs-6">
                    <?= date('M j') ?> - <?= date('M j', strtotime('+7 days')) ?>
                </span>
            </div>
        </div>

        <div class="row">
            <?php
            // Generate 7 days starting from today
            for ($i = 0; $i < 7; $i++) {
                $current_date = date('Y-m-d', strtotime("+$i days"));
                $day_name = date('l', strtotime($current_date));
                $day_date = date('M j', strtotime($current_date));
                $day_appointments = $schedule[$current_date] ?? [];
            ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card schedule-day h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-day me-2"></i>
                                <?= $day_name ?>
                                <small class="float-end"><?= $day_date ?></small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($day_appointments)): ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No appointments</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($day_appointments as $appointment): ?>
                                    <div class="appointment-slot p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?= sanitize($appointment['patient_name']) ?></h6>
                                            <span class="badge bg-<?= $appointment['status'] === 'scheduled' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($appointment['status']) ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('g:i A', strtotime($appointment['appointment_time'])) ?>
                                        </p>
                                        <?php if ($appointment['patient_phone']): ?>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-phone me-1"></i>
                                                <?= sanitize($appointment['patient_phone']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="mb-0">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>
                                                <?= ucfirst($appointment['type']) ?>
                                            </small>
                                        </p>
                                        <?php if ($appointment['symptoms']): ?>
                                            <p class="mb-0 mt-2">
                                                <small class="text-muted">
                                                    <strong>Symptoms:</strong> <?= sanitize(substr($appointment['symptoms'], 0, 50)) ?>...
                                                </small>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($day_appointments)): ?>
                            <div class="card-footer text-center">
                                <small class="text-muted"><?= count($day_appointments) ?> appointment(s)</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= count($appointments) ?></h3>
                        <p class="text-muted mb-0">Total This Week</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">
                            <?= count(array_filter($appointments, fn($a) => $a['appointment_date'] === date('Y-m-d'))) ?>
                        </h3>
                        <p class="text-muted mb-0">Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info">
                            <?= count(array_filter($appointments, fn($a) => $a['appointment_date'] === date('Y-m-d', strtotime('+1 day')))) ?>
                        </h3>
                        <p class="text-muted mb-0">Tomorrow</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>