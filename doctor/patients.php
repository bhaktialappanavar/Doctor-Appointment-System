<?php
require_once 'config.php';
require_role('doctor');

// Handle search
$search = trim($_GET['search'] ?? '');

// Get doctor's patients
if ($search) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.*, pp.date_of_birth, pp.gender, pp.blood_group, 
               COUNT(a.id) as total_appointments,
               MAX(a.appointment_date) as last_visit
        FROM users u 
        JOIN appointments a ON u.id = a.patient_id
        LEFT JOIN patient_profiles pp ON u.id = pp.user_id
        WHERE a.doctor_id = ? AND u.name LIKE ?
        GROUP BY u.id
        ORDER BY last_visit DESC
    ");
    $stmt->execute([$_SESSION['user_id'], '%' . $search . '%']);
} else {
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.*, pp.date_of_birth, pp.gender, pp.blood_group, 
               COUNT(a.id) as total_appointments,
               MAX(a.appointment_date) as last_visit
        FROM users u 
        JOIN appointments a ON u.id = a.patient_id
        LEFT JOIN patient_profiles pp ON u.id = pp.user_id
        WHERE a.doctor_id = ?
        GROUP BY u.id
        ORDER BY last_visit DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
}
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patients - MediCare</title>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2><i class="fas fa-users me-2"></i>My Patients</h2>
                <p class="text-muted mb-0">Patients who have appointments with you</p>
            </div>
            <div class="col-md-4">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control" placeholder="Search by patient name..." value="<?= sanitize($search) ?>">
                    <button type="submit" class="btn btn-primary ms-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if ($search): ?>
                        <a href="patients.php" class="btn btn-secondary ms-1">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="row">
            <?php if (empty($patients)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-<?= $search ? 'search' : 'users' ?> fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted"><?= $search ? 'No patients found' : 'No patients yet' ?></h4>
                            <p class="text-muted">
                                <?= $search ? 'Try searching with a different name' : 'Patients will appear here once they book appointments with you' ?>
                            </p>
                            <?php if ($search): ?>
                                <a href="patients.php" class="btn btn-primary">Show All Patients</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($patients as $patient): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= sanitize($patient['name']) ?></h6>
                                        <small class="text-muted"><?= sanitize($patient['email']) ?></small>
                                    </div>
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
                                    <div class="mb-2">
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
                                            <?= $patient['last_visit'] ? date('M j', strtotime($patient['last_visit'])) : 'Never' ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <a href="patient_profile.php?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-info me-1">
                                        <i class="fas fa-eye me-1"></i>View Profile
                                    </a>
                                    <a href="add_medical_record.php?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i>Add Record
                                    </a>
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