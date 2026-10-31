<?php
    require_once __DIR__ . '/../auth.php';

    $error              = '';
    $username           = '';
    $password           = '';
    $konfirmasiPassword = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username           = trim($_POST['username'] ?? '');
        $password           = $_POST['password'] ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Username dan password wajib diisi.';
        } elseif (! preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $error = 'Username hanya boleh terdiri dari huruf, angka, dan underscore (_) dengan panjang 3 - 50 karakter.';
        } elseif (strlen($password) < 5) {
            $error = 'Password minimal harus terdiri dari 5 karakter.';
        } elseif ($password !== $konfirmasiPassword) {
            $error = 'Konfirmasi password tidak cocok dengan password baru.';
        } else {
            // Cek apakah username sudah digunakan
            $stmtCek = mysqli_prepare(
                $conn,
                "SELECT id_user FROM users WHERE username = ? LIMIT 1"
            );
            mysqli_stmt_bind_param($stmtCek, "s", $username);
            mysqli_stmt_execute($stmtCek);
            mysqli_stmt_store_result($stmtCek);

            if (mysqli_stmt_num_rows($stmtCek) > 0) {
                $error = 'Username "' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '" sudah digunakan. Silakan gunakan username lain.';
                mysqli_stmt_close($stmtCek);
            } else {
                mysqli_stmt_close($stmtCek);

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO users (username, password) VALUES (?, ?)"
                );
                mysqli_stmt_bind_param($stmt, "ss", $username, $hash);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: index.php?status=ditambah');
                    exit;
                }

                $error = 'Gagal menambahkan admin baru. Silakan coba lagi.';
                mysqli_stmt_close($stmt);
            }
        }
    }

    $pageTitle = 'Tambah Admin';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Tambah Admin</h4>
    <p class="text-secondary mb-0">
        Daftarkan akun administrator baru untuk mengelola sistem inventaris.
    </p>
</div>

<div class="card summary-card">
    <div class="card-body p-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">
                    Username Admin <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-control"
                    maxlength="50"
                    placeholder="Contoh: admin_lab"
                    value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                    required
                    autocomplete="username"
                >
                <div class="form-text">
                    Gunakan 3-50 karakter (huruf, angka, atau garis bawah).
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    Password <span class="text-danger">*</span>
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    minlength="5"
                    placeholder="Masukkan password"
                    required
                    autocomplete="new-password"
                >
                <div class="form-text">
                    Minimal 5 karakter.
                </div>
            </div>

            <div class="mb-4">
                <label for="konfirmasi_password" class="form-label">
                    Konfirmasi Password <span class="text-danger">*</span>
                </label>
                <input
                    type="password"
                    name="konfirmasi_password"
                    id="konfirmasi_password"
                    class="form-control"
                    minlength="5"
                    placeholder="Ulangi password"
                    required
                    autocomplete="new-password"
                >
                <div class="form-text">
                    Pastikan password sama dengan isian di atas.
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Simpan Admin
                </button>
                <a href="index.php" class="btn btn-light border">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../assets/footer.php'; ?>
