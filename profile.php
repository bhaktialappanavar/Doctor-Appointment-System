<?php
require_once 'config.php';
require_login();

$user = get_user_info();
$success = '';
$error = '';

// Get role-specific profile data
$profile = null;
try {
    if ($_SESSION['user_role'] === 'doctor') {
        $stmt = $pdo->prepare("
            SELECT dp.*, s.name as specialty_name 
            FROM doctor_profiles dp 
            LEFT JOIN specialties s ON dp.specialty_id = s.id 
            WHERE dp.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $profile = $stmt->fetch();
    } elseif ($_SESSION['user_role'] === 'patient') {
        $stmt = $pdo->prepare("SELECT * FROM patient_profiles WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $profile = $stmt->fetch();
    }
} catch (PDOException $e) {
    // Table might not exist, continue without profile data
    $profile = null;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        
        try {
            // Handle file upload
            $avatar_path = $user['avatar']; // Keep existing avatar by default
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_extensions) && $_FILES['avatar']['size'] <= 2097152) {
                    $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                        // Delete old avatar if exists
                        if ($user['avatar'] && file_exists($user['avatar'])) {
                            unlink($user['avatar']);
                        }
                        $avatar_path = 'uploads/' . $new_filename; // Store relative path
                    } else {
                        $error = 'Failed to upload image';
                    }
                } else {
                    $error = 'Invalid file type or size too large';
                }
            } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = 'Upload error occurred';
            }
            
            // Update user info
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $avatar_path, $_SESSION['user_id']]);
            
            // Update role-specific profile
            if ($_SESSION['user_role'] === 'doctor') {
                try {
                    $specialty_id = (int)($_POST['specialty_id'] ?? 0) ?: null;
                    
                    // Handle custom specialty
                    if ($_POST['specialty_id'] === 'custom' && !empty($_POST['custom_specialty'])) {
                        $custom_specialty = sanitize($_POST['custom_specialty']);
                        // Check if specialty already exists
                        $stmt = $pdo->prepare("SELECT id FROM specialties WHERE name = ?");
                        $stmt->execute([$custom_specialty]);
                        $existing = $stmt->fetch();
                        
                        if ($existing) {
                            $specialty_id = $existing['id'];
                        } else {
                            // Create new specialty
                            $stmt = $pdo->prepare("INSERT INTO specialties (name, description) VALUES (?, ?)");
                            $stmt->execute([$custom_specialty, 'Specialized medical practice and consultation']);
                            $specialty_id = $pdo->lastInsertId();
                        }
                    }
                    
                    $experience_years = (int)($_POST['experience_years'] ?? 0);
                    $qualification = sanitize($_POST['qualification'] ?? '');
                    $consultation_fee = (float)($_POST['consultation_fee'] ?? 0);
                    $license_number = sanitize($_POST['license_number'] ?? '');
                    
                    // Create table if it doesn't exist
                    $pdo->exec("CREATE TABLE IF NOT EXISTS doctor_profiles (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        specialty_id INT,
                        license_number VARCHAR(50),
                        experience_years INT DEFAULT 0,
                        qualification TEXT,
                        consultation_fee DECIMAL(10,2) DEFAULT 0
                    )");
                    
                    if ($profile) {
                        $stmt = $pdo->prepare("
                            UPDATE doctor_profiles 
                            SET specialty_id = ?, experience_years = ?, qualification = ?, consultation_fee = ?, license_number = ?
                            WHERE user_id = ?
                        ");
                        $stmt->execute([$specialty_id, $experience_years, $qualification, $consultation_fee, $license_number, $_SESSION['user_id']]);
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO doctor_profiles (user_id, specialty_id, experience_years, qualification, consultation_fee, license_number) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$_SESSION['user_id'], $specialty_id, $experience_years, $qualification, $consultation_fee, $license_number]);
                    }
                } catch (PDOException $e) {
                    // Continue if doctor profile update fails
                }
            } elseif ($_SESSION['user_role'] === 'patient') {
                try {
                    $dob = $_POST['date_of_birth'] ?? null;
                    $gender = $_POST['gender'] ?? null;
                    $address = sanitize($_POST['address'] ?? '');
                    $blood_group = sanitize($_POST['blood_group'] ?? '');
                    
                    // Try to create table if it doesn't exist
                    $pdo->exec("CREATE TABLE IF NOT EXISTS patient_profiles (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        date_of_birth DATE,
                        gender ENUM('male', 'female', 'other'),
                        address TEXT,
                        blood_group VARCHAR(5)
                    )");
                    
                    if ($profile) {
                        $stmt = $pdo->prepare("
                            UPDATE patient_profiles 
                            SET date_of_birth = ?, gender = ?, address = ?, blood_group = ? 
                            WHERE user_id = ?
                        ");
                        $stmt->execute([$dob, $gender, $address, $blood_group, $_SESSION['user_id']]);
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO patient_profiles (user_id, date_of_birth, gender, address, blood_group) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$_SESSION['user_id'], $dob, $gender, $address, $blood_group]);
                    }
                } catch (PDOException $e) {
                    // If patient_profiles operations fail, just update user info
                }
            }
            
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $user = get_user_info();
            
            // Refresh profile data
            if ($_SESSION['user_role'] === 'doctor') {
                $stmt = $pdo->prepare("
                    SELECT dp.*, s.name as specialty_name 
                    FROM doctor_profiles dp 
                    LEFT JOIN specialties s ON dp.specialty_id = s.id 
                    WHERE dp.user_id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $profile = $stmt->fetch();
            } elseif ($_SESSION['user_role'] === 'patient') {
                $stmt = $pdo->prepare("SELECT * FROM patient_profiles WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $profile = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = 'Failed to update profile. Please try again.';
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
    <title>Profile - MediCare</title>
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
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if ($user['avatar'] && file_exists($user['avatar'])): ?>
                            <img src="<?= sanitize($user['avatar']) ?>" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;" alt="Profile Photo">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        <h5><?= sanitize($user['name']) ?></h5>
                        <p class="text-muted"><?= ucfirst($user['role']) ?></p>
                        <p class="text-muted">
                            <i class="fas fa-envelope me-1"></i>
                            <?= sanitize($user['email']) ?>
                        </p>
                        <?php if ($user['phone']): ?>
                            <p class="text-muted">
                                <i class="fas fa-phone me-1"></i>
                                <?= sanitize($user['phone']) ?>
                            </p>
                        <?php endif; ?>
                        <small class="text-muted">
                            Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Profile Information
                        </h5>
                        <button class="btn btn-outline-primary btn-sm" onclick="toggleEdit()" id="editBtn">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
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

                        <!-- View Mode -->
                        <div id="viewMode">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Full Name</label>
                                    <p class="form-control-plaintext"><?= sanitize($user['name']) ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <p class="form-control-plaintext"><?= sanitize($user['phone'] ?? 'Not provided') ?></p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <p class="form-control-plaintext"><?= sanitize($user['email']) ?></p>
                            </div>

                            <?php if ($_SESSION['user_role'] === 'patient'): ?>
                                <hr>
                                <h6>Patient Information</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Date of Birth</label>
                                        <p class="form-control-plaintext"><?= $profile['date_of_birth'] ? date('M j, Y', strtotime($profile['date_of_birth'])) : 'Not provided' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Gender</label>
                                        <p class="form-control-plaintext"><?= ucfirst($profile['gender'] ?? 'Not specified') ?></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Blood Group</label>
                                        <p class="form-control-plaintext"><?= sanitize($profile['blood_group'] ?? 'Not specified') ?></p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Address</label>
                                    <p class="form-control-plaintext"><?= sanitize($profile['address'] ?? 'Not provided') ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                                <hr>
                                <h6>Doctor Information</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Specialty</label>
                                        <p class="form-control-plaintext"><?= sanitize($profile['specialty_name'] ?? 'Not set') ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Experience</label>
                                        <p class="form-control-plaintext"><?= ($profile['experience_years'] ?? 0) ?> years</p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Qualification</label>
                                    <p class="form-control-plaintext"><?= sanitize($profile['qualification'] ?? 'Not provided') ?></p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Consultation Fee</label>
                                        <p class="form-control-plaintext">₹<?= number_format($profile['consultation_fee'] ?? 0, 2) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">License Number</label>
                                        <p class="form-control-plaintext"><?= sanitize($profile['license_number'] ?? 'Not provided') ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Edit Mode -->
                        <div id="editMode" style="display: none;">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="name" required value="<?= sanitize($user['name']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" name="avatar" accept="image/*">
                                    <small class="text-muted">Upload JPG, PNG or GIF (max 2MB)</small>
                                </div>

                                <?php if ($_SESSION['user_role'] === 'patient'): ?>
                                    <hr>
                                    <h6>Patient Information</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" name="date_of_birth" value="<?= $profile['date_of_birth'] ?? '' ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select" name="gender">
                                                <option value="">Select Gender</option>
                                                <option value="male" <?= ($profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                                <option value="female" <?= ($profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                                <option value="other" <?= ($profile['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Blood Group</label>
                                            <select class="form-select" name="blood_group">
                                                <option value="">Select Blood Group</option>
                                                <?php
                                                $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                                foreach ($blood_groups as $bg):
                                                ?>
                                                    <option value="<?= $bg ?>" <?= ($profile['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="3"><?= sanitize($profile['address'] ?? '') ?></textarea>
                                    </div>
                                <?php endif; ?>

                                <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                                    <hr>
                                    <h6>Doctor Information</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Specialty</label>
                                            <select class="form-select" name="specialty_id" id="specialtySelect" onchange="toggleCustomSpecialty()">
                                                <option value="">Select Specialty</option>
                                                <?php
                                                // First try to get from database
                                                try {
                                                    $stmt = $pdo->prepare("SELECT * FROM specialties ORDER BY name");
                                                    $stmt->execute();
                                                    $specialties = $stmt->fetchAll();
                                                } catch (PDOException $e) {
                                                    $specialties = [];
                                                }
                                                
                                                // If no specialties in database, use default list
                                                if (empty($specialties)) {
                                                    $default_specialties = [
                                                        'General Medicine', 'Cardiology', 'Dermatology', 'Orthopedics',
                                                        'Pediatrics', 'Gynecology', 'Neurology', 'Psychiatry',
                                                        'Ophthalmology', 'ENT', 'Gastroenterology', 'Pulmonology',
                                                        'Endocrinology', 'Nephrology', 'Oncology', 'Radiology',
                                                        'Anesthesiology', 'Emergency Medicine', 'Family Medicine',
                                                        'Internal Medicine', 'Surgery', 'Urology', 'Rheumatology'
                                                    ];
                                                    foreach ($default_specialties as $index => $specialty_name):
                                                ?>
                                                        <option value="<?= $index + 1 ?>"><?= $specialty_name ?></option>
                                                    <?php endforeach;
                                                } else {
                                                    foreach ($specialties as $specialty):
                                                ?>
                                                        <option value="<?= $specialty['id'] ?>" <?= ($profile['specialty_id'] ?? '') == $specialty['id'] ? 'selected' : '' ?>>
                                                            <?= sanitize($specialty['name']) ?>
                                                        </option>
                                                    <?php endforeach;
                                                } ?>
                                                <option value="custom">+ Add New Specialty</option>
                                            </select>
                                            <input type="text" class="form-control mt-2" name="custom_specialty" id="customSpecialty" placeholder="Enter new specialty" style="display: none;">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Experience (Years)</label>
                                            <input type="number" class="form-control" name="experience_years" value="<?= $profile['experience_years'] ?? 0 ?>" min="0">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Qualification</label>
                                        <textarea class="form-control" name="qualification" rows="2" placeholder="e.g., MBBS, MD"><?= sanitize($profile['qualification'] ?? '') ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Consultation Fee (₹)</label>
                                            <input type="number" class="form-control" name="consultation_fee" value="<?= $profile['consultation_fee'] ?? 0 ?>" min="0" step="0.01">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">License Number</label>
                                            <input type="text" class="form-control" name="license_number" value="<?= sanitize($profile['license_number'] ?? '') ?>">
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleEdit()">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleEdit() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            const editBtn = document.getElementById('editBtn');
            
            if (viewMode.style.display === 'none') {
                // Switch to view mode
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                editBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
            } else {
                // Switch to edit mode
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                editBtn.innerHTML = '<i class="fas fa-eye me-1"></i>View';
            }
        }
        
        function toggleCustomSpecialty() {
            const select = document.getElementById('specialtySelect');
            const customInput = document.getElementById('customSpecialty');
            
            if (select.value === 'custom') {
                customInput.style.display = 'block';
                customInput.required = true;
            } else {
                customInput.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
            }
        }
    </script>
</body>
</html>