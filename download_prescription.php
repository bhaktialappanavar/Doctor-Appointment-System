<?php
require_once 'config.php';
require_login();

$record_id = (int)($_GET['id'] ?? 0);

if (!$record_id) {
    header('Location: dashboard.php');
    exit;
}

// Check if user is doctor or patient viewing their own record
if ($_SESSION['user_role'] === 'patient') {
    // For patients, verify they own this medical record
    $stmt = $pdo->prepare("SELECT patient_id FROM medical_records WHERE id = ?");
    $stmt->execute([$record_id]);
    $record_check = $stmt->fetch();
    
    if (!$record_check || $record_check['patient_id'] != $_SESSION['user_id']) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied');
    }
} elseif ($_SESSION['user_role'] !== 'doctor') {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}

// Get medical record
$stmt = $pdo->prepare("
    SELECT mr.*, p.name as patient_name, p.phone as patient_phone, p.email as patient_email,
           d.name as doctor_name, dp.qualification, s.name as specialty_name
    FROM medical_records mr 
    JOIN users p ON mr.patient_id = p.id 
    JOIN users d ON mr.doctor_id = d.id
    LEFT JOIN doctor_profiles dp ON d.id = dp.user_id
    LEFT JOIN specialties s ON dp.specialty_id = s.id
    WHERE mr.id = ? " . ($_SESSION['user_role'] === 'doctor' ? 'AND mr.doctor_id = ?' : '') . "
");
if ($_SESSION['user_role'] === 'doctor') {
    $stmt->execute([$record_id, $_SESSION['user_id']]);
} else {
    $stmt->execute([$record_id]);
}
$record = $stmt->fetch();

if (!$record) {
    header('Location: patients.php');
    exit;
}

// Generate HTML content
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Prescription</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .doctor-info { text-align: center; margin-bottom: 20px; }
        .patient-info { margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .section h4 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>MediCare Prescription</h1>
    </div>
    
    <div class="doctor-info">
        <h3>Dr. ' . htmlspecialchars($record['doctor_name']) . '</h3>
        <p>' . htmlspecialchars($record['qualification'] ?? 'MBBS') . '</p>
        <p>' . htmlspecialchars($record['specialty_name'] ?? 'General Medicine') . '</p>
    </div>
    
    <div class="patient-info">
        <strong>Patient:</strong> ' . htmlspecialchars($record['patient_name']) . '<br>
        <strong>Phone:</strong> ' . htmlspecialchars($record['patient_phone'] ?? 'N/A') . '<br>
        <strong>Date:</strong> ' . date('F j, Y', strtotime($record['created_at'])) . '
    </div>
    
    <div class="prescription">';

if ($record['diagnosis']) {
    $html .= '
        <div class="section">
            <h4>Diagnosis</h4>
            <p>' . nl2br(htmlspecialchars($record['diagnosis'])) . '</p>
        </div>';
}

if ($record['prescription']) {
    $html .= '
        <div class="section">
            <h4>Prescription</h4>
            <p>' . nl2br(htmlspecialchars($record['prescription'])) . '</p>
        </div>';
}

if ($record['treatment']) {
    $html .= '
        <div class="section">
            <h4>Treatment</h4>
            <p>' . nl2br(htmlspecialchars($record['treatment'])) . '</p>
        </div>';
}

if ($record['notes']) {
    $html .= '
        <div class="section">
            <h4>Additional Notes</h4>
            <p>' . nl2br(htmlspecialchars($record['notes'])) . '</p>
        </div>';
}

$html .= '
    </div>
    
    <div class="footer">
        <p>This is a computer-generated prescription from MediCare System</p>
        <p>Generated on: ' . date('F j, Y g:i A') . '</p>
    </div>
    
    <script>
    window.onload = function() {
        window.print();
        setTimeout(function() {
            window.close();
        }, 1000);
    };
    </script>
</body>
</html>';

// Output HTML for print-to-PDF
echo $html;
?>