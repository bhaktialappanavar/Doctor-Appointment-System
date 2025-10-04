<?php
require_once 'config.php';
require_role('admin');

// Handle doctor approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    
    if ($action === 'approve' && $doctor_id) {
        $stmt = $pdo->prepare("UPDATE doctor_profiles SET is_verified = 1 WHERE user_id = ?");
        $stmt->execute([$doctor_id]);
        $success = "Doctor approved successfully!";
    } elseif ($action === 'reject' && $doctor_id) {
        $stmt = $pdo->prepare("UPDATE doctor_profiles SET is_verified = 0 WHERE user_id = ?");
        $stmt->execute([$doctor_id]);
        $success = "Doctor verification revoked!";
    }
}

// Get all doctors with their profiles
$stmt = $pdo->prepare("
    SELECT u.*, dp.qualification, dp.experience_years, dp.consultation_fee, dp.is_verified, s.name as specialty_name,
           COUNT(a.id) as total_appointments
    FROM users u 
    JOIN doctor_profiles dp ON u.id = dp.user_id
    LEFT JOIN specialties s ON dp.specialty_id = s.id
    LEFT JOIN appointments a ON u.id = a.doctor_id
    WHERE u.role = 'doctor'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - MediCare</title>
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
        <h2><i class="fas fa-user-md me-2"></i>Manage Doctors</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($doctors as $doctor): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0">Dr. <?= sanitize($doctor['name']) ?></h6>
                                <span class="badge bg-<?= $doctor['is_verified'] ? 'success' : 'warning' ?>">
                                    <?= $doctor['is_verified'] ? 'Verified' : 'Pending' ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?= sanitize($doctor['email']) ?>
                                </small>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>
                                    <?= sanitize($doctor['phone'] ?? 'Not provided') ?>
                                </small>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-stethoscope me-1"></i>
                                    <?= sanitize($doctor['specialty_name'] ?? 'General Medicine') ?>
                                </small>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    <?= sanitize($doctor['qualification'] ?? 'MBBS') ?>
                                </small>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= $doctor['experience_years'] ?? 0 ?> years experience
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-rupee-sign me-1"></i>
                                    ₹<?= number_format($doctor['consultation_fee'] ?? 0) ?> consultation fee
                                </small>
                            </div>
                            
                            <hr>
                            
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Appointments</small>
                                    <div class="fw-bold"><?= $doctor['total_appointments'] ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Joined</small>
                                    <div class="fw-bold"><?= date('M Y', strtotime($doctor['created_at'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <?php if (!$doctor['is_verified']): ?>
                                    <form method="POST" class="flex-fill">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="fas fa-check me-1"></i>Approve
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="flex-fill">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-warning w-100">
                                            <i class="fas fa-times me-1"></i>Revoke
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>