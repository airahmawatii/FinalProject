<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
require_once __DIR__ . '/../app/config/config.php';
header("Location: " . BASE_URL . "/index.php");
exit;
