
<?php
    require_once __DIR__ . '/../auth.php';

    $query = mysqli_query(
    $conn,
    "SELECT
        k.id_kategori,
        k.nama_kategori,
        k.keterangan,
        COUNT(b.id_barang) AS total_jenis
     FROM kategori k
     LEFT JOIN barang b ON b.id_kategori = k.id_kategori
     GROUP BY k.id_kategori, k.nama_kategori, k.keterangan
     ORDER BY k.id_kategori ASC"
    );

    $pageTitle = 'Data Kategori';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="d-flex flex-wrap justify-content-between
            align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Kategori</h4>
        <p class="text-secondary mb-0">
            Kelola kategori inventaris sekolah.
        </p>
    </div>

    <a href="tambah.php" class="btn btn-primary">
        + Tambah Kategori
    </a>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'ditambah'): ?>
    <div class="alert alert-success">Kategori berhasil ditambahkan.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'diedit'): ?>
    <div class="alert alert-success">Kategori berhasil diperbarui.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'dihapus'): ?>
    <div class="alert alert-success">Kategori berhasil dihapus.</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'digunakan'): ?>
    <div class="alert alert-danger">
        Kategori tidak bisa dihapus karena masih digunakan oleh barang.
    </div>
<?php endif; ?>

<div class="card summary-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No.</th>
                        <th>ID Kategori</th>
                        <th>Nama Kategori</th>
                        <th>Keterangan</th>
                        <th>Jenis Barang</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="ps-4"><?php echo $no++ ?></td>

                            <td>
                                <span class="badge text-bg-primary">
                                    <?php echo htmlspecialchars($row['id_kategori'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>

                            <td class="fw-semibold">
                                <?php echo htmlspecialchars($row['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['keterangan'] ?? '', ENT_QUOTES, 'UTF-8') ?: '-' ?>
                            </td>

                            <td><?php echo (int) $row['total_jenis'] ?></td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a
                                        href="edit.php?id=<?php echo urlencode($row['id_kategori']) ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="hapus.php"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus kategori ini?')"
                                    >
                                        <input
                                            type="hidden"
                                            name="id_kategori"
                                            value="<?php echo htmlspecialchars($row['id_kategori'], ENT_QUOTES, 'UTF-8') ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            Belum ada kategori. Silakan tambahkan kategori pertama.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../assets/footer.php'; ?>
