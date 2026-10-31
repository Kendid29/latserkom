<?php
    require_once __DIR__ . '/../auth.php';

    $cari = trim($_GET['cari'] ?? '');

    $sql = "SELECT id_user, username FROM users WHERE 1=1";
    $types = '';
    $params = [];

    if ($cari !== '') {
        $sql .= " AND username LIKE ?";
        $keyword = '%' . $cari . '%';
        $types .= 's';
        $params[] = $keyword;
    }

    $sql .= " ORDER BY id_user ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $adminQuery = mysqli_stmt_get_result($stmt);

    $pageTitle = 'Data Admin';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Admin</h4>
        <p class="text-secondary mb-0">
            Kelola data dan hak akses administrator sistem inventaris.
        </p>
    </div>

    <a href="tambah.php" class="btn btn-primary">
        + Tambah Admin
    </a>
</div>

<?php if (($_GET['status'] ?? '') === 'ditambah'): ?>
    <div class="alert alert-success">Admin baru berhasil ditambahkan.</div>
<?php elseif (($_GET['status'] ?? '') === 'diedit'): ?>
    <div class="alert alert-success">Data admin berhasil diperbarui.</div>
<?php elseif (($_GET['status'] ?? '') === 'dihapus'): ?>
    <div class="alert alert-success">Admin berhasil dihapus.</div>
<?php elseif (($_GET['error'] ?? '') === 'sendiri'): ?>
    <div class="alert alert-danger">Anda tidak dapat menghapus akun sendiri yang sedang aktif digunakan.</div>
<?php elseif (($_GET['error'] ?? '') === 'terakhir'): ?>
    <div class="alert alert-danger">Admin tidak dapat dihapus karena harus tersisa minimal 1 akun admin dalam sistem.</div>
<?php elseif (($_GET['error'] ?? '') === 'notfound'): ?>
    <div class="alert alert-danger">Data admin tidak ditemukan.</div>
<?php endif; ?>

<div class="card summary-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <input
                    type="search"
                    name="cari"
                    class="form-control"
                    placeholder="Cari berdasarkan username..."
                    value="<?php echo htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Cari
                </button>
                <?php if ($cari !== ''): ?>
                    <a href="index.php" class="btn btn-light border">
                        Reset
                    </a>
                <?php endif; ?>
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
                        <th class="ps-4" style="width: 70px;">No.</th>
                        <th>ID Admin</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (mysqli_num_rows($adminQuery) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($adminQuery)): ?>
                        <?php $isCurrent = ((int) $row['id_user'] === (int) $_SESSION['id_user']); ?>
                        <tr>
                            <td class="ps-4"><?php echo $no++; ?></td>
                            <td>
                                <span class="badge text-bg-primary">
                                    #<?php echo htmlspecialchars($row['id_user'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td class="fw-semibold">
                                <?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td>
                                <?php if ($isCurrent): ?>
                                    <span class="badge text-bg-success">Akun Anda</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Administrator</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a
                                        href="edit.php?id=<?php echo urlencode($row['id_user']); ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <?php if ($isCurrent): ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            disabled
                                            title="Anda tidak dapat menghapus akun Anda sendiri"
                                        >
                                            Hapus
                                        </button>
                                    <?php else: ?>
                                        <form
                                            action="hapus.php"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin <?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?>?')"
                                        >
                                            <input
                                                type="hidden"
                                                name="id_user"
                                                value="<?php echo htmlspecialchars($row['id_user'], ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary">
                            Data admin tidak ditemukan.
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
