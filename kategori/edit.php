
<?php
    require_once __DIR__ . '/../auth.php';

    $idAwal = trim($_GET['id'] ?? '');

    if ($idAwal === '') {
    header('Location: index.php');
    exit;
    }

    $stmt = mysqli_prepare(
    $conn,
    "SELECT id_kategori, nama_kategori, keterangan
     FROM kategori WHERE id_kategori = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $idAwal);
    mysqli_stmt_execute($stmt);

    $result   = mysqli_stmt_get_result($stmt);
    $kategori = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (! $kategori) {
    header('Location: index.php');
    exit;
    }

    $error        = '';
    $namaKategori = $kategori['nama_kategori'];
    $keterangan   = $kategori['keterangan'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaKategori = trim($_POST['nama_kategori'] ?? '');
    $keterangan   = trim($_POST['keterangan'] ?? '');

    if ($namaKategori === '') {
        $error = 'Nama kategori wajib diisi.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE kategori
             SET nama_kategori = ?, keterangan = ?
             WHERE id_kategori = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $namaKategori,
            $keterangan,
            $idAwal
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: index.php?status=diedit');
            exit;
        }

        if (mysqli_stmt_errno($stmt) === 1062) {
            $error = 'Nama kategori tersebut sudah digunakan.';
        } else {
            $error = 'Kategori gagal diperbarui.';
        }

        mysqli_stmt_close($stmt);
    }
    }

    $pageTitle = 'Edit Kategori';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Edit Kategori</h4>
    <p class="text-secondary mb-0">
        Perbarui informasi kategori yang dipilih.
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
                <label class="form-label">ID Kategori</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars($kategori['id_kategori'], ENT_QUOTES, 'UTF-8') ?>"
                    disabled
                >
                <div class="form-text">
                    ID kategori tidak dapat diubah melalui halaman ini.
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
                ><?php echo htmlspecialchars($keterangan, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
                <a href="index.php" class="btn btn-light border">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../assets/footer.php'; ?>
