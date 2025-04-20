<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /tooltrack/login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /tooltrack/unauthorized.php');
        exit();
    }
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    
    // Update last login
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}
?>