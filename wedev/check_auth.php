<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /index.html');
    exit();
}
// Luego en cada modulo ponemos lo siguiente:
//
// require_once __DIR__ . '/../auth/check_auth.php';
