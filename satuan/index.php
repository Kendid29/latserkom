<?php
    require_once __DIR__ . '/../auth.php';

    $error = '';
    $success = '';

    // Tambah satuan baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
        $namaSatuan = strtolower(trim($_POST['nama_satuan'] ?? ''));

        if ($namaSatuan === '') {
            $error = 'Nama satuan tidak boleh kosong.';
        } elseif (strlen($namaSatuan) > 30) {
            $error = 'Nama satuan maksimal 30 karakter.';
        } elseif (! preg_match('/^[a-zA-Z0-9_\-\s]+$/', $namaSatuan)) {
            $error = 'Nama satuan hanya boleh huruf, angka, spasi, atau tanda strip (-).';
        } else {
            $stmtCek = mysqli_prepare($conn, "SELECT id_satuan FROM satuan WHERE nama_satuan = ?");
            mysqli_stmt_bind_param($stmtCek, 's', $namaSatuan);
            mysqli_stmt_execute($stmtCek);
            mysqli_stmt_store_result($stmtCek);

            if (mysqli_stmt_num_rows($stmtCek) > 0) {
                $error = 'Satuan "' . htmlspecialchars($namaSatuan, ENT_QUOTES, 'UTF-8') . '" sudah ada di daftar.';
                mysqli_stmt_close($stmtCek);
            } else {
                mysqli_stmt_close($stmtCek);
                $stmt = mysqli_prepare($conn, "INSERT INTO satuan (nama_satuan) VALUES (?)");
                mysqli_stmt_bind_param($stmt, 's', $namaSatuan);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: index.php?status=ditambah');
                    exit;
                }
                $error = 'Gagal menambahkan satuan.';
                mysqli_stmt_close($stmt);
            }
        }
    }

    // Ambil daftar satuan beserta jumlah barang yang memakainya
    $query = mysqli_query(
        $conn,
        "SELECT s.id_satuan, s.nama_satuan, COUNT(b.id_barang) AS total_barang
         FROM satuan s
         LEFT JOIN barang b ON b.satuan = s.nama_satuan
         GROUP BY s.id_satuan, s.nama_satuan
         ORDER BY s.nama_satuan ASC"
    );

    $pageTitle = 'Kelola Satuan';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Kelola Satuan Barang</h4>
        <p class="text-secondary mb-0">
            Daftar satuan unit barang yang tersedia di sistem inventaris.
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalTambahSatuan"
    >
        + Tambah Satuan
    </button>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'ditambah'): ?>
    <div class="alert alert-success">Satuan baru berhasil ditambahkan.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'dihapus'): ?>
    <div class="alert alert-success">Satuan berhasil dihapus.</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'digunakan'): ?>
    <div class="alert alert-danger">
        Satuan tidak bisa dihapus karena masih digunakan oleh data barang.
    </div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card summary-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 70px;">No.</th>
                                <th>Nama Satuan</th>
                                <th>Digunakan Oleh</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (mysqli_num_rows($query) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td class="ps-4"><?php echo $no++; ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary px-3 py-2 fs-6">
                                            <?php echo htmlspecialchars($row['nama_satuan'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ((int) $row['total_barang'] > 0): ?>
                                            <span class="badge text-bg-primary">
                                                <?php echo (int) $row['total_barang']; ?> barang
                                            </span>
                                        <?php else: ?>
                                            <small class="text-secondary">Belum digunakan</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int) $row['total_barang'] > 0): ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                disabled
                                                title="Satuan masih digunakan oleh barang"
                                            >
                                                Hapus
                                            </button>
                                        <?php else: ?>
                                            <form
                                                action="hapus.php"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus satuan \'<?php echo htmlspecialchars($row['nama_satuan'], ENT_QUOTES, 'UTF-8'); ?>\'?')"
                                                class="d-inline"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id_satuan"
                                                    value="<?php echo (int) $row['id_satuan']; ?>"
                                                >
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                >
                                                    Hapus
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-secondary">
                                    Belum ada data satuan.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card summary-card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Informasi Satuan</h6>
                <p class="text-secondary small mb-3">
                    Satuan yang ditambahkan di sini akan otomatis muncul sebagai pilihan pada dropdown saat Admin menambah atau mengedit barang.
                </p>
                <div class="p-3 bg-light rounded-3 small text-secondary">
                    <span class="fw-semibold text-dark d-block mb-1">Catatan Keamanan:</span>
                    Satuan yang sudah dipakai oleh data barang tidak dapat dihapus untuk menjaga konsistensi data inventaris.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Satuan -->
<div class="modal fade" id="modalTambahSatuan" tabindex="-1" aria-labelledby="modalTambahSatuanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTambahSatuanLabel">Tambah Satuan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="tambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_satuan" class="form-label">
                            Nama Satuan <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_satuan"
                            id="nama_satuan"
                            class="form-control"
                            maxlength="30"
                            placeholder="Contoh: rim, liter, botol, roll"
                            required
                            autofocus
                        >
                        <div class="form-text">
                            Gunakan huruf kecil atau singkatan standar.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Satuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../assets/footer.php'; ?>
