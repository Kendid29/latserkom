
<?php
    require_once __DIR__ . '/../auth.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
    }

    $idBarang = trim($_POST['id_barang'] ?? '');

    if ($idBarang === '') {
    header('Location: index.php');
    exit;
    }

    $stmt = mysqli_prepare(
    $conn,
    "DELETE FROM barang WHERE id_barang = ?"
    );

    mysqli_stmt_bind_param($stmt, 's', $idBarang);
    mysqli_stmt_execute($stmt);

    $berhasil = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($berhasil) {
    header('Location: index.php?status=dihapus');
    } else {
    header('Location: index.php');
    }

    exit;
?>
