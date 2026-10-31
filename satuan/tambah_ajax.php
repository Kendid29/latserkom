<?php
    require_once __DIR__ . '/../auth.php';

    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
        exit;
    }

    $namaSatuan = strtolower(trim($_POST['nama_satuan'] ?? ''));

    if ($namaSatuan === '') {
        echo json_encode(['success' => false, 'message' => 'Nama satuan tidak boleh kosong.']);
        exit;
    }

    if (strlen($namaSatuan) > 30) {
        echo json_encode(['success' => false, 'message' => 'Nama satuan maksimal 30 karakter.']);
        exit;
    }

    if (! preg_match('/^[a-zA-Z0-9_\-\s]+$/', $namaSatuan)) {
        echo json_encode(['success' => false, 'message' => 'Nama satuan hanya boleh huruf, angka, spasi, atau tanda strip (-).']);
        exit;
    }

    // Cek apakah sudah ada
    $stmtCek = mysqli_prepare($conn, "SELECT id_satuan, nama_satuan FROM satuan WHERE nama_satuan = ?");
    mysqli_stmt_bind_param($stmtCek, 's', $namaSatuan);
    mysqli_stmt_execute($stmtCek);
    $res = mysqli_stmt_get_result($stmtCek);
    $existing = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmtCek);

    if ($existing) {
        // Jika sudah ada, langsung kembalikan sukses dengan nama tersebut
        echo json_encode([
            'success'     => true,
            'id_satuan'   => (int) $existing['id_satuan'],
            'nama_satuan' => $existing['nama_satuan'],
            'message'     => 'Satuan sudah ada dan telah dipilih.'
        ]);
        exit;
    }

    // Simpan satuan baru
    $stmt = mysqli_prepare($conn, "INSERT INTO satuan (nama_satuan) VALUES (?)");
    mysqli_stmt_bind_param($stmt, 's', $namaSatuan);

    if (mysqli_stmt_execute($stmt)) {
        $newId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        echo json_encode([
            'success'     => true,
            'id_satuan'   => $newId,
            'nama_satuan' => $namaSatuan,
            'message'     => 'Satuan baru berhasil ditambahkan.'
        ]);
        exit;
    }

    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan satuan baru ke database.']);
    exit;
?>
