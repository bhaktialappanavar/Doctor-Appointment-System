<?php
require_once 'config.php';
require_role('admin');

// Get all patients with their profiles
$stmt = $pdo->prepare("
    SELECT u.*, pp.date_of_birth, pp.gender, pp.blood_group, pp.address,
           COUNT(a.id) as total_appointments,
           MAX(a.appointment_date) as last_appointment
    FROM users u 
    LEFT JOIN patient_profiles pp ON u.id = pp.user_id
    LEFT JOIN appointments a ON u.id = a.patient_id
    WHERE u.role = 'patient'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - MediCare</title>
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
        <h2><i class="fas fa-users me-2"></i>Manage Patients</h2>

        <div class="row">
            <?php if (empty($patients)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No patients registered</h4>
                            <p class="text-muted">Patients will appear here once they register</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($patients as $patient): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="mb-0"><?= sanitize($patient['name']) ?></h6>
                                    <span class="badge bg-<?= ($patient['is_active'] ?? 1) ? 'success' : 'secondary' ?>">
                                        <?= ($patient['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?= sanitize($patient['email']) ?>
                                    </small>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-1"></i>
                                        <?= sanitize($patient['phone'] ?? 'Not provided') ?>
                                    </small>
                                </div>
                                
                                <?php if ($patient['date_of_birth']): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-birthday-cake me-1"></i>
                                            <?= date('M j, Y', strtotime($patient['date_of_birth'])) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($patient['gender']): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-venus-mars me-1"></i>
                                            <?= ucfirst($patient['gender']) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($patient['blood_group']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-tint me-1"></i>
                                            <?= sanitize($patient['blood_group']) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Appointments</small>
                                        <div class="fw-bold"><?= $patient['total_appointments'] ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Last Visit</small>
                                        <div class="fw-bold">
                                            <?= $patient['last_appointment'] ? date('M Y', strtotime($patient['last_appointment'])) : 'Never' ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row text-center">
                                    <div class="col-12">
                                        <small class="text-muted">Joined</small>
                                        <div class="fw-bold"><?= date('M j, Y', strtotime($patient['created_at'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>