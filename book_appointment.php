<?php
require_once 'config.php';
require_role('patient');

$error = '';
$success = '';

// Get specialties for filtering
$stmt = $pdo->prepare("SELECT * FROM specialties ORDER BY name");
$stmt->execute();
$specialties = $stmt->fetchAll();

// Get doctors with filtering
$specialty_filter = $_GET['specialty'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "
    SELECT u.*, dp.*, s.name as specialty_name, s.icon as specialty_icon
    FROM users u 
    JOIN doctor_profiles dp ON u.id = dp.user_id 
    LEFT JOIN specialties s ON dp.specialty_id = s.id 
    WHERE u.role = 'doctor' AND u.status = 'active'
";
$params = [];

if ($specialty_filter) {
    $sql .= " AND dp.specialty_id = ?";
    $params[] = $specialty_filter;
}

if ($search) {
    $sql .= " AND u.name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY u.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $doctor_id = (int)($_POST['doctor_id'] ?? 0);
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $type = $_POST['type'] ?? 'consultation';
        $symptoms = sanitize($_POST['symptoms'] ?? '');
        
        if (!$doctor_id || !$appointment_date || !$appointment_time) {
            $error = 'Please fill in all required fields';
        } elseif (strtotime($appointment_date) < strtotime('today')) {
            $error = 'Appointment date cannot be in the past';
        } else {
            try {
                // Check if doctor exists and is available
                $stmt = $pdo->prepare("
                    SELECT u.*, dp.consultation_fee 
                    FROM users u 
                    JOIN doctor_profiles dp ON u.id = dp.user_id 
                    WHERE u.id = ? AND u.role = 'doctor' AND u.status = 'active'
                ");
                $stmt->execute([$doctor_id]);
                $doctor = $stmt->fetch();
                
                if (!$doctor) {
                    $error = 'Invalid doctor selected';
                } else {
                    // Check for conflicting appointments
                    $stmt = $pdo->prepare("
                        SELECT id FROM appointments 
                        WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? 
                        AND status IN ('scheduled', 'completed')
                    ");
                    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
                    
                    if ($stmt->fetch()) {
                        $error = 'This time slot is already booked. Please choose another time.';
                    } else {
                        // Create appointment
                        $stmt = $pdo->prepare("
                            INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, type, symptoms, fee) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $_SESSION['user_id'], 
                            $doctor_id, 
                            $appointment_date, 
                            $appointment_time, 
                            $type, 
                            $symptoms,
                            $doctor['consultation_fee']
                        ]);
                        
                        $success = 'Appointment booked successfully! You will receive a confirmation shortly.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Failed to book appointment. Please try again.';
            }
        }
    } else {
        $error = 'Invalid request';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - MediCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .doctor-card { transition: transform 0.2s; cursor: pointer; }
        .doctor-card:hover { transform: translateY(-2px); }
        .doctor-card.selected { border: 2px solid #667eea; background: #f8f9ff; }
        .time-slot { cursor: pointer; transition: all 0.2s; }
        .time-slot:hover { background: #e9ecef; }
        .time-slot.selected { background: #667eea; color: white; }
    </style>
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
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Book New Appointment</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            </div>
                        <?php endif; ?>

                        <!-- Doctor Search and Filter -->
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Search Doctors</label>
                                    <input type="text" class="form-control" name="search" placeholder="Doctor name..." value="<?= sanitize($search) ?>">
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
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Doctors List -->
                        <div class="row">
                            <?php if (empty($doctors)): ?>
                                <div class="col-12 text-center py-4">
                                    <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No doctors found</h5>
                                    <p class="text-muted">Try adjusting your search criteria</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($doctors as $doctor): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="doctor-card card h-100" onclick="selectDoctor(<?= $doctor['user_id'] ?>)">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-user-md"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Dr. <?= sanitize($doctor['name']) ?></h6>
                                                        <p class="text-muted mb-1">
                                                            <i class="<?= $doctor['specialty_icon'] ?? 'fas fa-stethoscope' ?> me-1"></i>
                                                            <?= sanitize($doctor['specialty_name'] ?? 'General Medicine') ?>
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <i class="fas fa-graduation-cap me-1"></i>
                                                            <?= $doctor['experience_years'] ?> years experience
                                                        </p>
                                                        <?php if ($doctor['consultation_fee']): ?>
                                                            <p class="text-success mb-0">
                                                                <i class="fas fa-rupee-sign me-1"></i>
                                                                ₹<?= number_format($doctor['consultation_fee'], 2) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Appointment Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="bookingForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="doctor_id" id="selectedDoctorId">
                            
                            <div class="mb-3">
                                <label class="form-label">Selected Doctor</label>
                                <div id="selectedDoctorInfo" class="text-muted">
                                    Please select a doctor from the list
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Appointment Date *</label>
                                <input type="date" class="form-control" name="appointment_date" required min="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Preferred Time *</label>
                                <div class="row g-2" id="timeSlots">
                                    <?php
                                    $times = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
                                    foreach ($times as $time):
                                    ?>
                                        <div class="col-6">
                                            <div class="time-slot border rounded p-2 text-center" onclick="selectTime('<?= $time ?>')">
                                                <?= date('g:i A', strtotime($time)) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="appointment_time" id="selectedTime">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Appointment Type</label>
                                <select class="form-select" name="type">
                                    <option value="consultation">Consultation</option>
                                    <option value="follow_up">Follow-up</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Symptoms/Reason</label>
                                <textarea class="form-control" name="symptoms" rows="3" placeholder="Describe your symptoms or reason for visit..."></textarea>
                            </div>
                            
                            <button type="submit" name="book_appointment" class="btn btn-primary w-100" disabled id="bookBtn">
                                <i class="fas fa-calendar-check me-2"></i>Book Appointment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedDoctor = null;
        let selectedTime = null;

        function selectDoctor(doctorId) {
            // Remove previous selection
            document.querySelectorAll('.doctor-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            event.currentTarget.classList.add('selected');
            
            // Get doctor info
            const doctorName = event.currentTarget.querySelector('h6').textContent;
            const specialty = event.currentTarget.querySelector('.text-muted').textContent.trim();
            
            // Update form
            document.getElementById('selectedDoctorId').value = doctorId;
            document.getElementById('selectedDoctorInfo').innerHTML = `
                <strong>${doctorName}</strong><br>
                <small>${specialty}</small>
            `;
            
            selectedDoctor = doctorId;
            updateBookButton();
        }

        function selectTime(time) {
            // Remove previous selection
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selection to clicked slot
            event.currentTarget.classList.add('selected');
            
            // Update form
            document.getElementById('selectedTime').value = time;
            selectedTime = time;
            updateBookButton();
        }

        function updateBookButton() {
            const bookBtn = document.getElementById('bookBtn');
            if (selectedDoctor && selectedTime) {
                bookBtn.disabled = false;
            } else {
                bookBtn.disabled = true;
            }
        }
    </script>
</body>
</html>