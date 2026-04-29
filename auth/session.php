<?php
session_start();

if (!isset($_SESSION["identifiant"])) {
    header('Location: ../auth/login.php');
    exit();
}
