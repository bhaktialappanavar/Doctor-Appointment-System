<?php
require_once 'config.php';
require_role('doctor');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $template_name = trim($_POST['template_name'] ?? '');
    $medicines = trim($_POST['medicines'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    
    if ($template_name && $medicines) {
        $stmt = $pdo->prepare("INSERT INTO prescription_templates (doctor_id, template_name, medicines, instructions) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $template_name, $medicines, $instructions]);
        $success = "Template saved successfully!";
    }
}

// Handle delete
if (isset($_GET['delete']) && verify_csrf($_GET['token'] ?? '')) {
    $stmt = $pdo->prepare("DELETE FROM prescription_templates WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    header('Location: prescription_templates.php');
    exit;
}

// Get templates
$stmt = $pdo->prepare("SELECT * FROM prescription_templates WHERE doctor_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$templates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Templates - MediCare</title>
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
        <h2><i class="fas fa-prescription-bottle me-2"></i>Prescription Templates</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Template</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Template Name</label>
                                <input type="text" name="template_name" class="form-control" placeholder="e.g., Common Cold" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Medicines</label>
                                <textarea name="medicines" class="form-control" rows="4" placeholder="1. Paracetamol 500mg - 1 tablet twice daily&#10;2. Cetirizine 10mg - 1 tablet at night" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Instructions</label>
                                <textarea name="instructions" class="form-control" rows="2" placeholder="Take after meals, avoid alcohol"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Template
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">My Templates</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($templates)): ?>
                            <p class="text-muted">No templates created yet.</p>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-2"><?= sanitize($template['template_name']) ?></h6>
                                        <a href="?delete=<?= $template['id'] ?>&token=<?= csrf_token() ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Delete this template?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Medicines:</strong><br>
                                        <small><?= nl2br(sanitize($template['medicines'])) ?></small>
                                    </div>
                                    <?php if ($template['instructions']): ?>
                                        <div>
                                            <strong>Instructions:</strong><br>
                                            <small><?= nl2br(sanitize($template['instructions'])) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>