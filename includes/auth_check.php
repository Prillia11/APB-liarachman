<?php
function checkAuth($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    
    if ($role && $_SESSION['role'] !== $role) {
        if ($_SESSION['role'] == 'admin') {
            header('Location: ../admin/index.php');
        } else {
            header('Location: ../user/index.php');
        }
        exit;
    }
}
