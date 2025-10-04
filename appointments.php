<?php
require_once 'config.php';
require_login();

// Get user's appointments based on role
if ($_SESSION['user_role'] === 'doctor') {
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as patient_name, u.phone as patient_phone
        FROM appointments a 
        JOIN users u ON a.patient_id = u.id 
        WHERE a.doctor_id = ? 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as doctor_name, s.name as specialty_name
        FROM appointments a 
        JOIN users u ON a.doctor_id = u.id 
        LEFT JOIN doctor_profiles dp ON u.id = dp.user_id
        LEFT JOIN specialties s ON dp.specialty_id = s.id
        WHERE a.patient_id = ? 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
}
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - MediCare</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-check me-2"></i>My Appointments</h2>
            <?php if ($_SESSION['user_role'] === 'patient'): ?>
                <a href="book_appointment.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Book New
                </a>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (empty($appointments)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No appointments found</h4>
                        <?php if ($_SESSION['user_role'] === 'patient'): ?>
                            <p class="text-muted">Book your first appointment to get started</p>
                            <a href="book_appointment.php" class="btn btn-primary">Book Appointment</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                                        <th>Patient</th>
                                        <th>Phone</th>
                                    <?php else: ?>
                                        <th>Doctor</th>
                                        <th>Specialty</th>
                                    <?php endif; ?>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                                            <td><?= sanitize($appointment['patient_name']) ?></td>
                                            <td><?= sanitize($appointment['patient_phone'] ?? 'N/A') ?></td>
                                        <?php else: ?>
                                            <td>Dr. <?= sanitize($appointment['doctor_name']) ?></td>
                                            <td><?= sanitize($appointment['specialty_name'] ?? 'General') ?></td>
                                        <?php endif; ?>
                                        <td><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></td>
                                        <td><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></td>
                                        <td><span class="badge bg-info"><?= ucfirst($appointment['type']) ?></span></td>
                                        <td>
                                            <span class="badge bg-<?= $appointment['status'] === 'scheduled' ? 'success' : ($appointment['status'] === 'completed' ? 'primary' : 'secondary') ?>">
                                                <?= ucfirst($appointment['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $appointment['fee'] ? '₹' . number_format($appointment['fee'], 2) : 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>