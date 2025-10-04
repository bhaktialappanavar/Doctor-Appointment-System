<?php
require_once 'config.php';
require_role('doctor');

$patient_id = (int)($_GET['patient_id'] ?? 0);

// Get patient info
$stmt = $pdo->prepare("
    SELECT u.*, pp.date_of_birth, pp.gender, pp.blood_group, pp.address
    FROM users u 
    LEFT JOIN patient_profiles pp ON u.id = pp.user_id
    WHERE u.id = ? AND u.role = 'patient'
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: patients.php');
    exit;
}

// Get patient's medical records from this doctor
$stmt = $pdo->prepare("
    SELECT mr.*, u.name as doctor_name
    FROM medical_records mr 
    JOIN users u ON mr.doctor_id = u.id
    WHERE mr.patient_id = ? AND mr.doctor_id = ?
    ORDER BY mr.created_at DESC
");
$stmt->execute([$patient_id, $_SESSION['user_id']]);
$records = $stmt->fetchAll();

// Get patient's appointments with this doctor
$stmt = $pdo->prepare("
    SELECT * FROM appointments 
    WHERE patient_id = ? AND doctor_id = ?
    ORDER BY appointment_date DESC, appointment_time DESC
    LIMIT 10
");
$stmt->execute([$patient_id, $_SESSION['user_id']]);
$appointments = $stmt->fetchAll();

// Get patient's health reports
$stmt = $pdo->prepare("
    SELECT * FROM health_reports 
    WHERE patient_id = ?
    ORDER BY upload_date DESC
");
$stmt->execute([$patient_id]);
$health_reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile - MediCare</title>
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
            <h2><i class="fas fa-user me-2"></i>Patient Profile</h2>
            <a href="patients.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Patients
            </a>
        </div>

        <div class="row">
            <!-- Patient Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Patient Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        </div>
                        
                        <h6 class="text-center mb-3"><?= sanitize($patient['name']) ?></h6>
                        
                        <div class="mb-2">
                            <strong>Email:</strong><br>
                            <?= sanitize($patient['email']) ?>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Phone:</strong><br>
                            <?= sanitize($patient['phone'] ?? 'Not provided') ?>
                        </div>
                        
                        <?php if ($patient['date_of_birth']): ?>
                            <div class="mb-2">
                                <strong>Date of Birth:</strong><br>
                                <?= date('M j, Y', strtotime($patient['date_of_birth'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($patient['gender']): ?>
                            <div class="mb-2">
                                <strong>Gender:</strong><br>
                                <?= ucfirst($patient['gender']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($patient['blood_group']): ?>
                            <div class="mb-2">
                                <strong>Blood Group:</strong><br>
                                <?= sanitize($patient['blood_group']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="add_medical_record.php?patient_id=<?= $patient['id'] ?>" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i>Add Medical Record
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Records & Appointments -->
            <div class="col-md-8">
                <!-- Medical Records -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Medical Records</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($records)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No medical records yet</h6>
                                <p class="text-muted">Add medical records after consultations</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <small class="text-muted">
                                            <?= date('M j, Y g:i A', strtotime($record['created_at'])) ?>
                                        </small>
                                        <a href="download_prescription.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-download me-1"></i>PDF
                                        </a>
                                    </div>
                                    
                                    <?php if ($record['diagnosis']): ?>
                                        <div class="mb-2">
                                            <strong class="text-primary">Diagnosis:</strong><br>
                                            <?= nl2br(sanitize($record['diagnosis'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['treatment']): ?>
                                        <div class="mb-2">
                                            <strong class="text-success">Treatment:</strong><br>
                                            <?= nl2br(sanitize($record['treatment'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['prescription']): ?>
                                        <div class="mb-2">
                                            <strong class="text-warning">Prescription:</strong><br>
                                            <?= nl2br(sanitize($record['prescription'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['notes']): ?>
                                        <div class="mb-0">
                                            <strong class="text-info">Notes:</strong><br>
                                            <?= nl2br(sanitize($record['notes'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Health Reports -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Health Reports</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($health_reports)): ?>
                            <p class="text-muted">No health reports uploaded</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($health_reports as $report): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="mb-2"><?= sanitize($report['report_name']) ?></h6>
                                                <p class="text-muted mb-2">
                                                    <small>
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= date('M j, Y', strtotime($report['upload_date'])) ?>
                                                    </small>
                                                </p>
                                                <div class="d-flex align-items-center">
                                                    <?php if (strpos($report['file_type'], 'image') !== false): ?>
                                                        <i class="fas fa-image text-primary me-2"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                                    <?php endif; ?>
                                                    <a href="<?= $report['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Appointments -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($appointments)): ?>
                            <p class="text-muted">No appointments found</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                            <tr>
                                                <td><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></td>
                                                <td><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></td>
                                                <td><?= ucfirst($appointment['type']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $appointment['status'] === 'scheduled' ? 'success' : ($appointment['status'] === 'completed' ? 'primary' : 'secondary') ?>">
                                                        <?= ucfirst($appointment['status']) ?>
                                                    </span>
                                                </td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>