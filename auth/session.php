<?php
include_once(__DIR__ . "/../config/config.php");

session_start();

if (!isset($_SESSION["identifiant"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}
