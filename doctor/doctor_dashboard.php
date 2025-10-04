<?php
if (!defined('INCLUDED_FROM_DASHBOARD')) {
    require_once 'config.php';
    require_role('doctor');
    define('INCLUDED_FROM_DASHBOARD', true);
}

// Check if doctor is verified (skip for admin)
$user = get_user_info();
if ($user['role'] === 'doctor' && isset($user['verification_status']) && $user['verification_status'] !== 'verified') {
    echo "<!DOCTYPE html><html><head><title>Verification Required</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='bg-light'><div class='container mt-5'><div class='alert alert-warning'><h4>Account Verification Required</h4><p>Your account is pending admin verification. Please wait for approval.</p><a href='logout.php' class='btn btn-secondary'>Logout</a></div></div></body></html>";
    exit;
}

// Get doctor's appointments
$stmt = $pdo->prepare("
    SELECT a.*, u.name as patient_name, u.phone as patient_phone
    FROM appointments a 
    JOIN users u ON a.patient_id = u.id 
    WHERE a.doctor_id = ? 
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 20
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
    WHERE doctor_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$counts = $stmt->fetch();
$scheduled_count = $counts['scheduled_count'] ?? 0;
$completed_count = $counts['completed_count'] ?? 0;
$cancelled_count = $counts['cancelled_count'] ?? 0;
$total_count = $counts['total_count'] ?? 0;

// Get total patients
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT patient_id) as count FROM appointments WHERE doctor_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$patients_count = $stmt->fetchColumn();

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $appointment_id = (int)($_POST['appointment_id'] ?? 0);
        $new_status = $_POST['status'] ?? '';
        
        if (in_array($new_status, ['scheduled', 'completed', 'cancelled', 'no_show'])) {
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
            $stmt->execute([$new_status, $appointment_id, $_SESSION['user_id']]);
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - MediCare</title>
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
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>My Appointments
                        </a>
                        <a class="nav-link" href="patients.php">
                            <i class="fas fa-users me-2"></i>My Patients
                        </a>
                        <a class="nav-link" href="schedule.php">
                            <i class="fas fa-clock me-2"></i>Schedule
                        </a>
                        <a class="nav-link" href="doctor_availability.php">
                            <i class="fas fa-calendar-alt me-2"></i>Availability
                        </a>
                        <a class="nav-link" href="prescription_templates.php">
                            <i class="fas fa-prescription-bottle me-2"></i>Templates
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
                            <h2 class="fw-bold">Welcome, Dr. <?= sanitize($_SESSION['user_name']) ?>!</h2>
                            <p class="text-muted">Here's your practice overview</p>
                        </div>
                        <div>
                            <span class="badge bg-success fs-6">Online</span>
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

                    <!-- Appointments -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Appointments</h5>
                            <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($appointments)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No appointments scheduled</h5>
                                    <p class="text-muted">Your appointments will appear here</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="appointment-card card mb-3">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h6 class="mb-1"><?= sanitize($appointment['patient_name']) ?></h6>
                                                    <p class="text-muted mb-1">
                                                        <i class="fas fa-phone me-1"></i>
                                                        <?= sanitize($appointment['patient_phone'] ?? 'No phone') ?>
                                                    </p>
                                                    <p class="text-muted mb-0">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?> at 
                                                        <?= date('g:i A', strtotime($appointment['appointment_time'])) ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="badge bg-<?= $appointment['status'] === 'scheduled' ? 'success' : ($appointment['status'] === 'completed' ? 'primary' : 'secondary') ?>">
                                                        <?= ucfirst($appointment['status']) ?>
                                                    </span>
                                                    <p class="text-muted mb-0 mt-1">
                                                        <small><?= ucfirst($appointment['type']) ?></small>
                                                    </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                        <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                            <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                            <option value="cancelled" <?= $appointment['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                            <option value="no_show" <?= $appointment['status'] === 'no_show' ? 'selected' : '' ?>>No Show</option>
                                                        </select>
                                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary mt-1">Update</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <?php if ($appointment['symptoms']): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Symptoms:</strong> <?= sanitize($appointment['symptoms']) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
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