<?php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: giris.php');
        exit;
    }
}

function getAdminUsername()
{
    return $_SESSION['admin_username'] ?? 'Admin';
}
