<?php
if (!defined('INCLUDED_FROM_DASHBOARD')) {
    require_once 'config.php';
    require_role('patient');
    define('INCLUDED_FROM_DASHBOARD', true);
}

// Get patient appointments
$stmt = $pdo->prepare("
    SELECT a.*, u.name as doctor_name, s.name as specialty_name, dp.consultation_fee
    FROM appointments a 
    JOIN users u ON a.doctor_id = u.id 
    LEFT JOIN doctor_profiles dp ON u.id = dp.user_id
    LEFT JOIN specialties s ON dp.specialty_id = s.id
    WHERE a.patient_id = ? 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();

// Get appointment counts by status
$stmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
        COUNT(*) as total_count
    FROM appointments 
    WHERE patient_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$counts = $stmt->fetch();
$scheduled_count = $counts['scheduled_count'] ?? 0;
$completed_count = $counts['completed_count'] ?? 0;
$cancelled_count = $counts['cancelled_count'] ?? 0;
$total_count = $counts['total_count'] ?? 0;

// Get medical records count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM medical_records WHERE patient_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$records_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { background: #2c3e50; min-height: 100vh; }
        .sidebar .nav-link { color: #bdc3c7; padding: 15px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #34495e; color: white; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; }
        .appointment-card { border-left: 4px solid #667eea; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3 text-center border-bottom">
                        <h5 class="text-white mb-0"><i class="fas fa-heartbeat me-2"></i>MediCare</h5>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="book_appointment.php">
                            <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                        </a>
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>My Appointments
                        </a>
                        <a class="nav-link" href="medical_records.php">
                            <i class="fas fa-file-medical me-2"></i>Medical Records
                        </a>
                        <a class="nav-link" href="doctors.php">
                            <i class="fas fa-user-md me-2"></i>Find Doctors
                        </a>
                        <a class="nav-link" href="health_reports.php">
                            <i class="fas fa-file-medical-alt me-2"></i>Health Reports
                        </a>
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <hr class="text-muted">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold">Welcome back, <?= sanitize($_SESSION['user_name']) ?>!</h2>
                            <p class="text-muted">Here's your health overview</p>
                        </div>
                        <div>
                            <a href="book_appointment.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Book Appointment
                            </a>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?= $scheduled_count ?></h3>
                                        <p class="mb-0">Scheduled</p>
                                    </div>
                                    <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?= $completed_count ?></h3>
                                        <p class="mb-0">Completed</p>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?= $total_count ?></h3>
                                        <p class="mb-0">Total Appointments</p>
                                    </div>
                                    <i class="fas fa-calendar fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Appointments</h5>
                            <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($appointments)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No appointments yet</h5>
                                    <p class="text-muted">Book your first appointment to get started</p>
                                    <a href="book_appointment.php" class="btn btn-primary">Book Now</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="appointment-card card mb-3">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <h6 class="mb-1">Dr. <?= sanitize($appointment['doctor_name']) ?></h6>
                                                    <p class="text-muted mb-1">
                                                        <i class="fas fa-stethoscope me-1"></i>
                                                        <?= sanitize($appointment['specialty_name'] ?? 'General Medicine') ?>
                                                    </p>
                                                    <p class="text-muted mb-0">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?> at 
                                                        <?= date('g:i A', strtotime($appointment['appointment_time'])) ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-md-end">
                                                    <span class="badge bg-<?= $appointment['status'] === 'scheduled' ? 'success' : ($appointment['status'] === 'completed' ? 'primary' : 'secondary') ?> mb-2">
                                                        <?= ucfirst($appointment['status']) ?>
                                                    </span>
                                                    <?php if ($appointment['consultation_fee']): ?>
                                                        <p class="text-muted mb-0">Fee: $<?= number_format($appointment['consultation_fee'], 2) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>