
<?php
    require_once __DIR__ . '/../auth.php';

    $idBarang = trim($_GET['id'] ?? '');

    if ($idBarang === '') {
    header('Location: index.php');
    exit;
    }

    $stmt = mysqli_prepare(
    $conn,
    "SELECT id_barang, nama_barang, id_kategori,
            jumlah, satuan, kondisi, keterangan
     FROM barang
     WHERE id_barang = ?"
    );

    mysqli_stmt_bind_param($stmt, 's', $idBarang);
    mysqli_stmt_execute($stmt);

    $barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (! $barang) {
    header('Location: index.php');
    exit;
    }

    $error      = '';
    $namaBarang = $barang['nama_barang'];
    $jumlah     = (string) $barang['jumlah'];
    $satuan     = $barang['satuan'] ?? 'unit';
    $kondisi    = $barang['kondisi'];
    $keterangan = $barang['keterangan'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBarang  = trim($_POST['nama_barang'] ?? '');
    $jumlahInput = trim($_POST['jumlah'] ?? '');
    $satuan      = trim($_POST['satuan'] ?? 'unit');
    $kondisi     = $_POST['kondisi'] ?? '';
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($satuan === '') {
        $satuan = 'unit';
    }

    $kondisiValid = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    if ($namaBarang === '') {
        $error = 'Nama barang wajib diisi.';
    } elseif (
        ! ctype_digit($jumlahInput) ||
        (float) $jumlahInput > 4294967295
    ) {
        $error = 'Jumlah harus berupa bilangan bulat nol atau lebih.';
    } elseif (! in_array($kondisi, $kondisiValid, true)) {
        $error = 'Kondisi barang tidak valid.';
    } else {
        $jumlah = (int) $jumlahInput;

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE barang
             SET nama_barang = ?, jumlah = ?, satuan = ?,
                 kondisi = ?, keterangan = ?
             WHERE id_barang = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'sissss',
            $namaBarang,
            $jumlah,
            $satuan,
            $kondisi,
            $keterangan,
            $idBarang
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: index.php?status=diedit');
            exit;
        }

        $error = 'Data barang gagal diperbarui.';
        mysqli_stmt_close($stmt);
    }
    }

    $pageTitle = 'Edit Barang';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Edit Barang</h4>
    <p class="text-secondary mb-0">
        Perbarui informasi barang inventaris.
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
                <label class="form-label">ID Barang</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars($barang['id_barang'], ENT_QUOTES, 'UTF-8') ?>"
                    disabled
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars($barang['id_kategori'], ENT_QUOTES, 'UTF-8') ?>"
                    disabled
                >
            </div>

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
                    required
                >
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
                            // Jika satuan barang saat ini belum ada di opsi bawaan, sertakan sebagai opsi
                            if (! in_array($satuan, $opsiSatuan, true)) {
                                $opsiSatuan[] = $satuan;
                            }
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
