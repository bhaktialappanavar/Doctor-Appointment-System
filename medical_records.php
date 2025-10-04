<?php
require_once 'config.php';
require_role('patient');

// Get patient's medical records
$stmt = $pdo->prepare("
    SELECT mr.*, u.name as doctor_name, s.name as specialty_name
    FROM medical_records mr 
    JOIN users u ON mr.doctor_id = u.id 
    LEFT JOIN doctor_profiles dp ON u.id = dp.user_id
    LEFT JOIN specialties s ON dp.specialty_id = s.id
    WHERE mr.patient_id = ? 
    ORDER BY mr.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - MediCare</title>
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
        <h2><i class="fas fa-file-medical me-2"></i>My Medical Records</h2>
        <p class="text-muted">Your complete medical history and treatment records</p>

        <?php if (empty($records)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-file-medical fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No medical records found</h4>
                    <p class="text-muted">Your medical records will appear here after doctor consultations</p>
                    <a href="book_appointment.php" class="btn btn-primary">Book Appointment</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($records as $record): ?>
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Dr. <?= sanitize($record['doctor_name']) ?></h6>
                            <small class="text-muted"><?= sanitize($record['specialty_name'] ?? 'General Medicine') ?></small>
                        </div>
                        <small class="text-muted"><?= date('M j, Y g:i A', strtotime($record['created_at'])) ?></small>
                    </div>
                    <div class="card-body">
                        <?php if ($record['diagnosis']): ?>
                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-diagnoses me-1"></i>Diagnosis</h6>
                                <p><?= nl2br(sanitize($record['diagnosis'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($record['treatment']): ?>
                            <div class="mb-3">
                                <h6 class="text-success"><i class="fas fa-procedures me-1"></i>Treatment</h6>
                                <p><?= nl2br(sanitize($record['treatment'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($record['prescription']): ?>
                            <div class="mb-3">
                                <h6 class="text-warning"><i class="fas fa-prescription-bottle me-1"></i>Prescription</h6>
                                <p><?= nl2br(sanitize($record['prescription'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($record['notes']): ?>
                            <div class="mb-3">
                                <h6 class="text-info"><i class="fas fa-sticky-note me-1"></i>Notes</h6>
                                <p class="mb-0"><?= nl2br(sanitize($record['notes'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-end">
                            <a href="download_prescription.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-download me-1"></i>Download Prescription
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>