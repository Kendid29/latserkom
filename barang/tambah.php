
<?php
    require_once __DIR__ . '/../auth.php';

    $error      = '';
    $namaBarang = '';
    $idKategori = '';
    $jumlah     = '1';
    $satuan     = 'pcs';
    $kondisi    = 'Baik';
    $keterangan = '';

    $kategoriQuery = mysqli_query(
    $conn,
    "SELECT id_kategori, nama_kategori
     FROM kategori
     ORDER BY nama_kategori ASC"
    );

    $kategoriList = mysqli_fetch_all($kategoriQuery, MYSQLI_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBarang  = trim($_POST['nama_barang'] ?? '');
    $idKategori  = trim($_POST['id_kategori'] ?? '');
    $jumlahInput = trim($_POST['jumlah'] ?? '');
    $satuan      = trim($_POST['satuan'] ?? 'pcs');
    $kondisi     = $_POST['kondisi'] ?? 'Baik';
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($satuan === '') {
        $satuan = 'unit';
    }

    $kondisiValid = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    if ($namaBarang === '' || $idKategori === '') {
        $error = 'Nama barang dan kategori wajib diisi.';
    } elseif (
        ! ctype_digit($jumlahInput) ||
        (float) $jumlahInput > 4294967295
    ) {
        $error = 'Jumlah harus berupa bilangan bulat nol atau lebih.';
    } elseif (! in_array($kondisi, $kondisiValid, true)) {
        $error = 'Pilihan kondisi tidak valid.';
    } else {
        $jumlah = (int) $jumlahInput;

        try {
            mysqli_begin_transaction($conn);

            // Kunci baris kategori agar pembuatan ID per kategori
            // berjalan bergantian pada transaksi bersamaan.
            $stmt = mysqli_prepare(
                $conn,
                "SELECT id_kategori
                 FROM kategori
                 WHERE id_kategori = ?
                 FOR UPDATE"
            );

            mysqli_stmt_bind_param($stmt, 's', $idKategori);
            mysqli_stmt_execute($stmt);

            $kategoriTerpilih = mysqli_fetch_assoc(
                mysqli_stmt_get_result($stmt)
            );

            mysqli_stmt_close($stmt);

            if (! $kategoriTerpilih) {
                throw new RuntimeException(
                    'Kategori yang dipilih tidak ditemukan.'
                );
            }

            // Contoh E1 menjadi BE1.
            $prefix = 'B' . $idKategori;

            // Cari nomor terbesar untuk kategori ini.
            $stmt = mysqli_prepare(
                $conn,
                "SELECT COALESCE(
                    MAX(CAST(SUBSTRING_INDEX(id_barang, '-', -1) AS UNSIGNED)),
                    0
                 ) AS nomor_terakhir
                 FROM barang
                 WHERE id_kategori = ?
                   AND id_barang REGEXP CONCAT('^B', ?, '-[0-9]+$')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'ss',
                $idKategori,
                $idKategori
            );

            mysqli_stmt_execute($stmt);

            $nomorData = mysqli_fetch_assoc(
                mysqli_stmt_get_result($stmt)
            );

            mysqli_stmt_close($stmt);

            $nomorBerikutnya = (int) $nomorData['nomor_terakhir'] + 1;

            // Lebih dari tiga digit diperbolehkan jika diperlukan.
            $idBarangBaru = $prefix . '-' .
            str_pad((string) $nomorBerikutnya, 3, '0', STR_PAD_LEFT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO barang
                    (id_barang, nama_barang, id_kategori,
                     jumlah, satuan, kondisi, keterangan)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'sssisss',
                $idBarangBaru,
                $namaBarang,
                $idKategori,
                $jumlah,
                $satuan,
                $kondisi,
                $keterangan
            );

            if (! mysqli_stmt_execute($stmt)) {
                throw new RuntimeException(
                    'Data barang gagal disimpan.'
                );
            }

            mysqli_stmt_close($stmt);
            mysqli_commit($conn);

            header('Location: index.php?status=ditambah');
            exit;

        } catch (Throwable $e) {
            mysqli_rollback($conn);

            // Detail teknis dicatat di log server, bukan ditampilkan.
            error_log($e->getMessage());

            $error = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Terjadi kesalahan saat menyimpan barang.';
        }
    }
    }

    $pageTitle = 'Tambah Barang';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Tambah Barang</h4>
    <p class="text-secondary mb-0">
        ID barang akan dibuat otomatis berdasarkan kategori.
    </p>
</div>

<div class="card summary-card">
    <div class="card-body p-4">

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (count($kategoriList) === 0): ?>
            <div class="alert alert-warning">
                Belum ada kategori.
                <a href="../kategori/tambah.php">Tambah kategori terlebih dahulu</a>.
            </div>
        <?php else: ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nama_barang" class="form-label">
                    Nama Barang
                </label>
                <input
                    type="text"
                    name="nama_barang"
                    id="nama_barang"
                    class="form-control"
                    maxlength="150"
                    value="<?php echo htmlspecialchars($namaBarang, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Contoh: Laptop Lenovo"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="id_kategori" class="form-label">
                    Kategori
                </label>
                <select
                    name="id_kategori"
                    id="id_kategori"
                    class="form-select"
                    required
                >
                    <option value="">-- Pilih Kategori --</option>

                    <?php foreach ($kategoriList as $kategori): ?>
                        <option
                            value="<?php echo htmlspecialchars($kategori['id_kategori'], ENT_QUOTES, 'UTF-8') ?>"
                            <?php echo $idKategori === $kategori['id_kategori'] ? 'selected' : '' ?>
                        >
                            <?php echo htmlspecialchars($kategori['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>
                            (<?php echo htmlspecialchars($kategori['id_kategori'], ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="form-text">
                    Contoh: kategori E1 menghasilkan awalan ID barang BE1.
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="jumlah" class="form-label">
                        Jumlah <span class="text-danger">*</span>
                    </label>
                    <input
                        type="number"
                        name="jumlah"
                        id="jumlah"
                        class="form-control"
                        min="0"
                        max="4294967295"
                        value="<?php echo htmlspecialchars((string) $jumlah, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="col-12 col-md-6">
                    <label for="satuan" class="form-label">
                        Satuan <span class="text-danger">*</span>
                    </label>
                    <select
                        name="satuan"
                        id="satuan"
                        class="form-select"
                        required
                    >
                        <?php
                            $opsiSatuan = ['pcs', 'unit', 'pack', 'box', 'dus', 'rim', 'liter', 'meter', 'kg', 'ekor', 'buah', 'set'];
                            foreach ($opsiSatuan as $opsi):
                        ?>
                            <option
                                value="<?php echo $opsi ?>"
                                <?php echo $satuan === $opsi ? 'selected' : '' ?>
                            >
                                <?php echo $opsi ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="kondisi" class="form-label">Kondisi</label>
                <select name="kondisi" id="kondisi" class="form-select" required>
                    <?php foreach (['Baik', 'Rusak Ringan', 'Rusak Berat'] as $pilihan): ?>
                        <option
                            value="<?php echo $pilihan ?>"
                            <?php echo $kondisi === $pilihan ? 'selected' : '' ?>
                        >
                            <?php echo $pilihan ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                    placeholder="Opsional"
                ><?php echo htmlspecialchars($keterangan, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Simpan Barang
                </button>
                <a href="index.php" class="btn btn-light border">
                    Batal
                </a>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../assets/footer.php'; ?>
