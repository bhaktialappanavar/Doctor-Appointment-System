<?php
require_once 'config.php';
require_role('doctor');

$patient_id = (int)($_GET['patient_id'] ?? 0);
$appointment_id = (int)($_GET['appointment_id'] ?? 0);

// Get patient info
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'patient'");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: patients.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $treatment = trim($_POST['treatment'] ?? '');
    $prescription = trim($_POST['prescription'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if ($diagnosis || $treatment || $prescription || $notes) {
        $stmt = $pdo->prepare("
            INSERT INTO medical_records (patient_id, doctor_id, appointment_id, diagnosis, treatment, prescription, notes, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$patient_id, $_SESSION['user_id'], $appointment_id ?: null, $diagnosis, $treatment, $prescription, $notes]);
        
        header('Location: patients.php?success=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medical Record - MediCare</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-medical me-2"></i>Add Medical Record</h5>
                        <small class="text-muted">Patient: <?= sanitize($patient['name']) ?></small>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-diagnoses me-1"></i>Diagnosis</label>
                                <textarea class="form-control" name="diagnosis" rows="3" placeholder="Enter diagnosis..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-procedures me-1"></i>Treatment</label>
                                <textarea class="form-control" name="treatment" rows="3" placeholder="Enter treatment given..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-prescription-bottle me-1"></i>Prescription</label>
                                <textarea class="form-control" name="prescription" rows="3" placeholder="Enter medicines prescribed..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-sticky-note me-1"></i>Additional Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Any additional notes..."></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Record
                                </button>
                                <a href="patients.php" class="btn btn-secondary">Cancel</a>
                                <button type="button" class="btn btn-info" onclick="loadTemplate()">
                                    <i class="fas fa-clipboard me-1"></i>Use Template
                                </button>
                            </div>
                            
                            <div id="templateModal" class="modal fade" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Select Template</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body" id="templateList">
                                            Loading templates...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function loadTemplate() {
        fetch('get_templates.php')
            .then(response => response.json())
            .then(data => {
                let html = '';
                data.forEach(template => {
                    html += `<div class="border rounded p-2 mb-2 cursor-pointer" onclick="useTemplate('${template.medicines}', '${template.instructions}')">
                        <strong>${template.template_name}</strong><br>
                        <small class="text-muted">${template.medicines.substring(0, 50)}...</small>
                    </div>`;
                });
                document.getElementById('templateList').innerHTML = html || 'No templates found';
                new bootstrap.Modal(document.getElementById('templateModal')).show();
            });
    }
    
    function useTemplate(medicines, instructions) {
        document.querySelector('textarea[name="prescription"]').value = medicines;
        document.querySelector('textarea[name="notes"]').value = instructions;
        bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
    }
    </script>
</body>
</html>