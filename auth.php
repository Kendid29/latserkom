<?php
require_once __DIR__ . "/koneksi.php";

if (! isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
