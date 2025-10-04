<?php
require_once 'config.php';
require_role('admin');

// Handle appointment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    if (in_array($new_status, ['scheduled', 'completed', 'cancelled', 'no_show']) && $appointment_id) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $appointment_id]);
        $success = "Appointment status updated!";
    }
}

// Get all appointments
$stmt = $pdo->prepare("
    SELECT a.*, p.name as patient_name, p.phone as patient_phone,
           d.name as doctor_name, s.name as specialty_name
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    JOIN users d ON a.doctor_id = d.id
    LEFT JOIN doctor_profiles dp ON d.id = dp.user_id
    LEFT JOIN specialties s ON dp.specialty_id = s.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 100
");
$stmt->execute();
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Appointments - MediCare</title>
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
        <h2><i class="fas fa-calendar-check me-2"></i>All Appointments</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No appointments found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td>
                                            <strong><?= sanitize($appointment['patient_name']) ?></strong><br>
                                            <small class="text-muted"><?= sanitize($appointment['patient_phone'] ?? 'No phone') ?></small>
                                        </td>
                                        <td>
                                            <strong>Dr. <?= sanitize($appointment['doctor_name']) ?></strong><br>
                                            <small class="text-muted"><?= sanitize($appointment['specialty_name'] ?? 'General Medicine') ?></small>
                                        </td>
                                        <td>
                                            <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?><br>
                                            <small class="text-muted"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= ucfirst($appointment['type']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $appointment['status'] === 'scheduled' ? 'success' : ($appointment['status'] === 'completed' ? 'primary' : 'secondary') ?>">
                                                <?= ucfirst($appointment['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                    <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="cancelled" <?= $appointment['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    <option value="no_show" <?= $appointment['status'] === 'no_show' ? 'selected' : '' ?>>No Show</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>