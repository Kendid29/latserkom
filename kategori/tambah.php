<?php
    require_once __DIR__ . '/../auth.php';

    $error        = '';
    $idKategori   = '';
    $namaKategori = '';
    $keterangan   = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idKategori   = strtoupper(trim($_POST['id_kategori'] ?? ''));
    $namaKategori = trim($_POST['nama_kategori'] ?? '');
    $keterangan   = trim($_POST['keterangan'] ?? '');

    if (
        ! preg_match('/^[A-Z][A-Z0-9]{0,9}$/', $idKategori) ||
        $namaKategori === ''
    ) {
        $error = 'ID kategori harus diawali huruf, maksimal 10 karakter, dan nama kategori wajib diisi.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO kategori (id_kategori, nama_kategori, keterangan)
             VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $idKategori,
            $namaKategori,
            $keterangan
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: index.php?status=ditambah');
            exit;
        }

        if (mysqli_stmt_errno($stmt) === 1062) {
            $error = 'ID kategori atau nama kategori sudah digunakan.';
        } else {
            $error = 'Kategori gagal disimpan. Periksa kembali data kamu.';
        }

        mysqli_stmt_close($stmt);
    }
    }

    $pageTitle = 'Tambah Kategori';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Tambah Kategori</h4>
    <p class="text-secondary mb-0">
        Masukkan identitas kategori barang baru.
    </p>
</div>

<div class="card summary-card">
    <div class="card-body p-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="id_kategori" class="form-label">
                    ID Kategori
                </label>

                <input
                    type="text"
                    name="id_kategori"
                    id="id_kategori"
                    class="form-control"
                    maxlength="10"
                    pattern="[A-Za-z][A-Za-z0-9]{0,9}"
                    placeholder="Contoh: E1"
                    value="<?php echo htmlspecialchars($idKategori, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

                <div class="form-text">
                    Diawali huruf, lalu boleh diikuti huruf atau angka.
                </div>
            </div>

            <div class="mb-3">
                <label for="nama_kategori" class="form-label">
                    Nama Kategori
                </label>

                <input
                    type="text"
                    name="nama_kategori"
                    id="nama_kategori"
                    class="form-control"
                    maxlength="100"
                    placeholder="Contoh: Elektronik"
                    value="<?php echo htmlspecialchars($namaKategori, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="keterangan" class="form-label">
                    Keterangan
                </label>

                <textarea
                    name="keterangan"
                    id="keterangan"
                    class="form-control"
                    rows="3"
                    placeholder="Keterangan tambahan (opsional)"
                ><?php echo htmlspecialchars($keterangan, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Simpan Kategori
                </button>

                <a href="index.php" class="btn btn-light border">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../assets/footer.php'; ?>
