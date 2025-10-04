<?php
require_once 'config.php';
require_role('doctor');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $day = $_POST['day'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if ($day) {
        // Check if slot already exists
        $stmt = $pdo->prepare("SELECT id FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?");
        $stmt->execute([$_SESSION['user_id'], $day]);
        
        if ($stmt->fetch()) {
            // Update existing
            $stmt = $pdo->prepare("UPDATE doctor_availability SET start_time = ?, end_time = ?, is_available = ? WHERE doctor_id = ? AND day_of_week = ?");
            $stmt->execute([$start_time, $end_time, $is_available, $_SESSION['user_id'], $day]);
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $day, $start_time, $end_time, $is_available]);
        }
        $success = "Availability updated successfully!";
    }
}

// Get current availability
$stmt = $pdo->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')");
$stmt->execute([$_SESSION['user_id']]);
$availability = $stmt->fetchAll();

$days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Availability - MediCare</title>
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
        <h2><i class="fas fa-clock me-2"></i>My Availability</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Weekly Schedule</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $availability_map = [];
                        foreach ($availability as $slot) {
                            $availability_map[$slot['day_of_week']] = $slot;
                        }
                        ?>
                        
                        <?php foreach ($days as $day_key => $day_name): ?>
                            <form method="POST" class="mb-3">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="day" value="<?= $day_key ?>">
                                
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <strong><?= $day_name ?></strong>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_available" class="form-check-input" id="available_<?= $day_key ?>" 
                                                   <?= ($availability_map[$day_key]['is_available'] ?? 1) ? 'checked' : '' ?> onchange="toggleTime('<?= $day_key ?>')">
                                            <label class="form-check-label" for="available_<?= $day_key ?>">Available</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="time" name="start_time" class="form-control time-input" id="start_<?= $day_key ?>"
                                               value="<?= $availability_map[$day_key]['start_time'] ?? '09:00' ?>" 
                                               <?= ($availability_map[$day_key]['is_available'] ?? 1) ? '' : 'disabled' ?>>
                                    </div>
                                    <div class="col-md-1 text-center">to</div>
                                    <div class="col-md-2">
                                        <input type="time" name="end_time" class="form-control time-input" id="end_<?= $day_key ?>"
                                               value="<?= $availability_map[$day_key]['end_time'] ?? '17:00' ?>" 
                                               <?= ($availability_map[$day_key]['is_available'] ?? 1) ? '' : 'disabled' ?>>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleTime(day) {
        const checkbox = document.getElementById('available_' + day);
        const startTime = document.getElementById('start_' + day);
        const endTime = document.getElementById('end_' + day);
        
        if (checkbox.checked) {
            startTime.disabled = false;
            endTime.disabled = false;
        } else {
            startTime.disabled = true;
            endTime.disabled = true;
        }
    }
    </script>
</body>
</html>