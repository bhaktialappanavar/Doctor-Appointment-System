<?php
require_once 'config.php';
require_role('doctor');

header('Content-Type: application/json');

$stmt = $pdo->prepare("SELECT template_name, medicines, instructions FROM prescription_templates WHERE doctor_id = ? ORDER BY template_name");
$stmt->execute([$_SESSION['user_id']]);
$templates = $stmt->fetchAll();

echo json_encode($templates);
?>