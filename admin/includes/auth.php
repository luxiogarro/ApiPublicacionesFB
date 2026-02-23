<?php
// admin/includes/auth.php
session_start();

function checkAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}
