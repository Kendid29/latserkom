<?php
session_start();

$host = "localhost";
$user = "inventaris_user";
$pass = "serkomrpl";
$db   = "inventaris_sekolah";

$conn = mysqli_connect($host, $user, $pass, $db);

if (! $conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
