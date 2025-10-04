<?php
require_once 'config.php';
require_login();

// Get search parameters
$search = sanitize($_GET['search'] ?? '');
$specialty_filter = (int)($_GET['specialty'] ?? 0);

// Get specialties for filter
$stmt = $pdo->prepare("SELECT * FROM specialties ORDER BY name");
$stmt->execute();
$specialties = $stmt->fetchAll();

// Get doctors with search and filter
$sql = "
    SELECT u.*, dp.*, s.name as specialty_name, s.icon as specialty_icon
    FROM users u 
    JOIN doctor_profiles dp ON u.id = dp.user_id 
    LEFT JOIN specialties s ON dp.specialty_id = s.id 
    WHERE u.role = 'doctor' AND u.status = 'active'
";
$params = [];

if ($search) {
    $sql .= " AND u.name LIKE ?";
    $params[] = "%$search%";
}

if ($specialty_filter) {
    $sql .= " AND dp.specialty_id = ?";
    $params[] = $specialty_filter;
}

$sql .= " ORDER BY u.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Doctors - MediCare</title>
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
        <div class="text-center mb-4">
            <h2>Find Your Specialist</h2>
            <p class="text-muted">Connect with the best healthcare professionals</p>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Search by name or specialty</label>
                            <input type="text" class="form-control" name="search" placeholder="Doctor name..." value="<?= $search ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Specialty</label>
                            <select class="form-select" name="specialty">
                                <option value="">All Specialties</option>
                                <?php foreach ($specialties as $specialty): ?>
                                    <option value="<?= $specialty['id'] ?>" <?= $specialty_filter == $specialty['id'] ? 'selected' : '' ?>>
                                        <?= sanitize($specialty['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Doctors List -->
        <div class="row">
            <?php if (empty($doctors)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-user-md fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No doctors found</h4>
                            <p class="text-muted">Try adjusting your search criteria</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($doctors as $doctor): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <?php if ($doctor['avatar'] && file_exists($doctor['avatar'])): ?>
                                        <img src="<?= sanitize($doctor['avatar']) ?>" class="rounded-circle mb-2" style="width: 60px; height: 60px; object-fit: cover;" alt="Doctor Photo">
                                    <?php else: ?>
                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h6 class="mb-1">Dr. <?= sanitize($doctor['name']) ?></h6>
                                    <p class="text-muted mb-2">
                                        <i class="<?= $doctor['specialty_icon'] ?? 'fas fa-stethoscope' ?> me-1"></i>
                                        <?= sanitize($doctor['specialty_name'] ?? 'General Medicine') ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        <?= $doctor['experience_years'] ?> years experience
                                    </small>
                                </div>
                                
                                <?php if ($doctor['qualification']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-certificate me-1"></i>
                                            <?= sanitize($doctor['qualification']) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($doctor['consultation_fee']): ?>
                                    <div class="mb-3">
                                        <span class="badge bg-success">
                                            <i class="fas fa-rupee-sign me-1"></i>
                                            ₹<?= number_format($doctor['consultation_fee'], 2) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($doctor['bio']): ?>
                                    <p class="text-muted small mb-3"><?= sanitize(substr($doctor['bio'], 0, 100)) ?>...</p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="book_appointment.php?doctor=<?= $doctor['user_id'] ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-calendar-plus me-1"></i>Book Appointment
                                </a>
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