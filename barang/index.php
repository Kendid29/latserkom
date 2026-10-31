
<?php
    require_once __DIR__ . '/../auth.php';

    $cari           = trim($_GET['cari'] ?? '');
    $filterKategori = trim($_GET['kategori'] ?? '');
    $filterKondisi  = trim($_GET['kondisi'] ?? '');

    $kondisiValid = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    $sql = "SELECT b.id_barang, b.nama_barang, b.id_kategori,
               b.jumlah, b.satuan, b.kondisi, b.keterangan,
               k.nama_kategori
        FROM barang b
        INNER JOIN kategori k ON k.id_kategori = b.id_kategori
        WHERE 1=1";

    $types  = '';
    $params = [];

    if ($cari !== '') {
    $sql      .= " AND (b.id_barang LIKE ? OR b.nama_barang LIKE ?)";
    $keyword   = '%' . $cari . '%';
    $types    .= 'ss';
    $params[]  = $keyword;
    $params[]  = $keyword;
    }

    if ($filterKategori !== '') {
    $sql      .= " AND b.id_kategori = ?";
    $types    .= 's';
    $params[]  = $filterKategori;
    }

    if (in_array($filterKondisi, $kondisiValid, true)) {
    $sql      .= " AND b.kondisi = ?";
    $types    .= 's';
    $params[]  = $filterKondisi;
    }

    $sql .= " ORDER BY b.id_barang ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $barangQuery = mysqli_stmt_get_result($stmt);

    $kategoriQuery = mysqli_query(
    $conn,
    "SELECT id_kategori, nama_kategori
     FROM kategori ORDER BY nama_kategori ASC"
    );

    $kategoriList = mysqli_fetch_all($kategoriQuery, MYSQLI_ASSOC);

    $pageTitle = 'Data Barang';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="d-flex flex-wrap justify-content-between
            align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Barang</h4>
        <p class="text-secondary mb-0">
            Daftar inventaris barang sekolah.
        </p>
    </div>

    <a href="tambah.php" class="btn btn-primary">
        + Tambah Barang
    </a>
</div>

<?php if (($_GET['status'] ?? '') === 'ditambah'): ?>
    <div class="alert alert-success">Barang berhasil ditambahkan.</div>
<?php elseif (($_GET['status'] ?? '') === 'diedit'): ?>
    <div class="alert alert-success">Data barang berhasil diperbarui.</div>
<?php elseif (($_GET['status'] ?? '') === 'dihapus'): ?>
    <div class="alert alert-success">Barang berhasil dihapus.</div>
<?php endif; ?>

<div class="card summary-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-12 col-lg-4">
                <label for="cari" class="form-label">Cari Barang</label>
                <input
                    type="search"
                    name="cari"
                    id="cari"
                    class="form-control"
                    placeholder="Nama atau ID barang"
                    value="<?php echo htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-12 col-lg-3">
                <label for="kategori" class="form-label">Kategori</label>
                <select name="kategori" id="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $kat): ?>
                        <option
                            value="<?php echo htmlspecialchars($kat['id_kategori'], ENT_QUOTES, 'UTF-8') ?>"
                            <?php echo $filterKategori === $kat['id_kategori'] ? 'selected' : '' ?>
                        >
                            <?php echo htmlspecialchars($kat['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-lg-3">
                <label for="kondisi" class="form-label">Kondisi</label>
                <select name="kondisi" id="kondisi" class="form-select">
                    <option value="">Semua Kondisi</option>
                    <?php foreach ($kondisiValid as $pilihan): ?>
                        <option
                            value="<?php echo $pilihan ?>"
                            <?php echo $filterKondisi === $pilihan ? 'selected' : '' ?>
                        >
                            <?php echo $pilihan ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-lg-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    Cari
                </button>
                <a href="index.php" class="btn btn-light border">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card summary-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No.</th>
                        <th>ID Barang</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (mysqli_num_rows($barangQuery) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($barangQuery)): ?>
                        <tr>
                            <td class="ps-4"><?php echo $no++ ?></td>
                            <td>
                                <span class="badge text-bg-primary">
                                    <?php echo htmlspecialchars($row['id_barang'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="fw-semibold">
                                <?php echo htmlspecialchars($row['nama_barang'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td><?php echo (int) $row['jumlah'] . ' ' . htmlspecialchars($row['satuan'] ?? 'unit', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php
                                    $badgeClass = match ($row['kondisi']) {
                                        'Baik'         => 'text-bg-success',
                                        'Rusak Ringan' => 'text-bg-warning',
                                        default        => 'text-bg-danger'
                                    };
                                ?>
                                <span class="badge <?php echo $badgeClass ?>">
                                    <?php echo htmlspecialchars($row['kondisi'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a
                                        href="edit.php?id=<?php echo urlencode($row['id_barang']) ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >Edit</a>

                                    <form
                                        action="hapus.php"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus barang ini?')"
                                    >
                                        <input
                                            type="hidden"
                                            name="id_barang"
                                            value="<?php echo htmlspecialchars($row['id_barang'], ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            Data barang tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    mysqli_stmt_close($stmt);
    require_once __DIR__ . '/../assets/footer.php';
?>
