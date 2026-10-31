<?php
    require_once __DIR__ . '/../auth.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php');
        exit;
    }

    $idUser = (int) ($_POST['id_user'] ?? 0);

    if ($idUser <= 0) {
        header('Location: index.php');
        exit;
    }

    // Keamanan 1: Cegah admin menghapus akun miliknya sendiri yang sedang aktif
    if ($idUser === (int) $_SESSION['id_user']) {
        header('Location: index.php?error=sendiri');
        exit;
    }

    // Keamanan 2: Pastikan masih ada sisa minimal 1 admin di dalam sistem
    $resCount = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
    $totalAdmin = (int) mysqli_fetch_assoc($resCount)['total'];

    if ($totalAdmin <= 1) {
        header('Location: index.php?error=terakhir');
        exit;
    }

    // Hapus admin
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, "i", $idUser);
    mysqli_stmt_execute($stmt);

    $berhasil = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($berhasil) {
        header('Location: index.php?status=dihapus');
    } else {
        header('Location: index.php?error=notfound');
    }
    exit;
?>
