<?php
    require_once __DIR__ . '/../auth.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php');
        exit;
    }

    $idSatuan = (int) ($_POST['id_satuan'] ?? 0);

    if ($idSatuan <= 0) {
        header('Location: index.php');
        exit;
    }

    // Ambil nama satuan terlebih dahulu
    $stmt = mysqli_prepare($conn, "SELECT nama_satuan FROM satuan WHERE id_satuan = ?");
    mysqli_stmt_bind_param($stmt, 'i', $idSatuan);
    mysqli_stmt_execute($stmt);
    $satuan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (! $satuan) {
        header('Location: index.php');
        exit;
    }

    // Cek apakah satuan masih digunakan oleh barang
    $stmtCek = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM barang WHERE satuan = ?");
    mysqli_stmt_bind_param($stmtCek, 's', $satuan['nama_satuan']);
    mysqli_stmt_execute($stmtCek);
    $dataCek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));
    mysqli_stmt_close($stmtCek);

    if ((int) $dataCek['total'] > 0) {
        header('Location: index.php?error=digunakan');
        exit;
    }

    // Hapus satuan jika tidak digunakan
    $stmtDel = mysqli_prepare($conn, "DELETE FROM satuan WHERE id_satuan = ?");
    mysqli_stmt_bind_param($stmtDel, 'i', $idSatuan);
    mysqli_stmt_execute($stmtDel);
    $berhasil = mysqli_stmt_affected_rows($stmtDel) > 0;
    mysqli_stmt_close($stmtDel);

    if ($berhasil) {
        header('Location: index.php?status=dihapus');
    } else {
        header('Location: index.php');
    }
    exit;
?>
