<?php
require_once 'config.php';
require_role('patient');

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $report_name = trim($_POST['report_name'] ?? '');
    
    if ($report_name && isset($_FILES['report_file']) && $_FILES['report_file']['error'] === 0) {
        $file = $_FILES['report_file'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $upload_dir = 'uploads/reports/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'report_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $stmt = $pdo->prepare("INSERT INTO health_reports (patient_id, report_name, file_path, file_type) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $report_name, $file_path, $file['type']]);
                $success = "Report uploaded successfully!";
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Invalid file type or size too large (max 5MB).";
        }
    } else {
        $error = "Please provide report name and select a file.";
    }
}

// Handle delete
if (isset($_GET['delete']) && verify_csrf($_GET['token'] ?? '')) {
    $stmt = $pdo->prepare("SELECT file_path FROM health_reports WHERE id = ? AND patient_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    $report = $stmt->fetch();
    
    if ($report && file_exists($report['file_path'])) {
        unlink($report['file_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM health_reports WHERE id = ? AND patient_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    header('Location: health_reports.php');
    exit;
}

// Get reports
$stmt = $pdo->prepare("SELECT * FROM health_reports WHERE patient_id = ? ORDER BY upload_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Reports - MediCare</title>
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
        <h2><i class="fas fa-file-medical-alt me-2"></i>Health Reports</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Upload New Report</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Report Name</label>
                                <input type="text" name="report_name" class="form-control" placeholder="e.g., Blood Test Report" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Select File</label>
                                <input type="file" name="report_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="text-muted">Supported: JPG, PNG, PDF (Max 5MB)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>Upload Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">My Reports</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reports)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-medical-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No reports uploaded</h5>
                                <p class="text-muted">Upload your lab reports, X-rays, and other medical documents</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($reports as $report): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0"><?= sanitize($report['report_name']) ?></h6>
                                                    <a href="?delete=<?= $report['id'] ?>&token=<?= csrf_token() ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Delete this report?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                                
                                                <p class="text-muted mb-2">
                                                    <small>
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= date('M j, Y g:i A', strtotime($report['upload_date'])) ?>
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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>