<?php
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/auth/login.php');
        exit();
    }
}

function getCurrentAdmin() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, username, email FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createAdmin($username, $email, $password) {
    $db = getDBConnection();
    
    // Check if username or email already exists
    $stmt = $db->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        return false;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $email, $hashed_password]);
}

function loginAdmin($username, $password) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        return true;
    }
    
    return false;
}

function logoutAdmin() {
    session_unset();
    session_destroy();
}