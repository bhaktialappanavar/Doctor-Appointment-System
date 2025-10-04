<?php
if (!defined('INCLUDED_FROM_DASHBOARD')) {
    require_once 'config.php';
    require_role('admin');
    define('INCLUDED_FROM_DASHBOARD', true);
}

// Get statistics
$stats = [];

// Total users by role
$stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stmt->execute();
while ($row = $stmt->fetch()) {
    $stats[$row['role']] = $row['count'];
}

// Appointment statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_appointments,
        COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_appointments,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_appointments,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_appointments
    FROM appointments
");
$stmt->execute();
$appointment_stats = $stmt->fetch();
$stats['appointments'] = $appointment_stats['total_appointments'];
$stats['scheduled'] = $appointment_stats['scheduled_appointments'];
$stats['completed'] = $appointment_stats['completed_appointments'];
$stats['cancelled'] = $appointment_stats['cancelled_appointments'];

// Recent appointments
$stmt = $pdo->prepare("
    SELECT a.*, p.name as patient_name, d.name as doctor_name 
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    JOIN users d ON a.doctor_id = d.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { background: #2c3e50; min-height: 100vh; }
        .sidebar .nav-link { color: #bdc3c7; padding: 15px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #34495e; color: white; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; }
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
                        <a class="nav-link" href="manage_users.php">
                            <i class="fas fa-users me-2"></i>All Users
                        </a>
                        <a class="nav-link" href="manage_doctors.php">
                            <i class="fas fa-user-md me-2"></i>Doctors
                        </a>
                        <a class="nav-link" href="manage_patients.php">
                            <i class="fas fa-users me-2"></i>Patients
                        </a>
                        <a class="nav-link" href="view_appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>Appointments
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
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
                    <h2 class="fw-bold mb-4">Admin Dashboard</h2>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?= $stats['patient'] ?? 0 ?></h3>
                                        <p class="mb-0">Patients</p>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?= $stats['doctor'] ?? 0 ?></h3>
                                        <p class="mb-0">Doctors</p>
                                    </div>
                                    <i class="fas fa-user-md fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?= $stats['scheduled'] ?? 0 ?></h3>
                                        <p class="mb-0">Scheduled</p>
                                    </div>
                                    <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?= $stats['completed'] ?? 0 ?></h3>
                                        <p class="mb-0">Completed</p>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Appointments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_appointments)): ?>
                                <p class="text-muted">No appointments found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_appointments as $appointment): ?>
                                                <tr>
                                                    <td><?= sanitize($appointment['patient_name']) ?></td>
                                                    <td>Dr. <?= sanitize($appointment['doctor_name']) ?></td>
                                                    <td>
                                                        <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?><br>
                                                        <small class="text-muted"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $appointment['status'] === 'scheduled' ? 'success' : ($appointment['status'] === 'completed' ? 'primary' : 'secondary') ?>">
                                                            <?= ucfirst($appointment['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= ucfirst($appointment['type']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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