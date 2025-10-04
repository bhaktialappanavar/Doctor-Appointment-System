<?php
require_once 'config.php';
require_login();

$user = get_user_info();

// Redirect based on role
switch ($_SESSION['user_role']) {
    case 'admin':
        include 'admin_dashboard.php';
        break;
    case 'doctor':
        include 'doctor_dashboard.php';
        break;
    case 'patient':
        include 'patient_dashboard.php';
        break;
    default:
        header('Location: login.php');
        exit;
}
?>