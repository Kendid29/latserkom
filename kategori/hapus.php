
<?php
    require_once __DIR__ . '/../auth.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
    }

    $idKategori = trim($_POST['id_kategori'] ?? '');

    if ($idKategori === '') {
    header('Location: index.php');
    exit;
    }

    // Periksa apakah kategori masih digunakan
    $stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM barang
     WHERE id_kategori = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $idKategori);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ((int) $data['total'] > 0) {
    header('Location: index.php?error=digunakan');
    exit;
    }

    // Hapus kategori jika tidak memiliki barang terkait
    $stmt = mysqli_prepare(
    $conn,
    "DELETE FROM kategori WHERE id_kategori = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $idKategori);
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
