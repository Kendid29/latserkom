<?php
    require_once __DIR__ . '/../auth.php';

    $idUser = (int) ($_GET['id'] ?? 0);

    if ($idUser <= 0) {
        header('Location: index.php?error=notfound');
        exit;
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id_user, username FROM users WHERE id_user = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $idUser);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (! $admin) {
        header('Location: index.php?error=notfound');
        exit;
    }

    $error              = '';
    $username           = $admin['username'];
    $password           = '';
    $konfirmasiPassword = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username           = trim($_POST['username'] ?? '');
        $password           = $_POST['password'] ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        if ($username === '') {
            $error = 'Username tidak boleh kosong.';
        } elseif (! preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $error = 'Username hanya boleh terdiri dari huruf, angka, dan underscore (_) dengan panjang 3 - 50 karakter.';
        } else {
            // Cek apakah username sudah dipakai oleh akun lain
            $stmtCek = mysqli_prepare(
                $conn,
                "SELECT id_user FROM users WHERE username = ? AND id_user != ? LIMIT 1"
            );
            mysqli_stmt_bind_param($stmtCek, "si", $username, $idUser);
            mysqli_stmt_execute($stmtCek);
            mysqli_stmt_store_result($stmtCek);

            if (mysqli_stmt_num_rows($stmtCek) > 0) {
                $error = 'Username "' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '" sudah digunakan oleh akun lain.';
                mysqli_stmt_close($stmtCek);
            } else {
                mysqli_stmt_close($stmtCek);

                // Periksa apakah ingin update password
                if ($password !== '') {
                    if (strlen($password) < 5) {
                        $error = 'Password baru minimal harus terdiri dari 5 karakter.';
                    } elseif ($password !== $konfirmasiPassword) {
                        $error = 'Konfirmasi password tidak cocok dengan password baru.';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmtUpdate = mysqli_prepare(
                            $conn,
                            "UPDATE users SET username = ?, password = ? WHERE id_user = ?"
                        );
                        mysqli_stmt_bind_param($stmtUpdate, "ssi", $username, $hash, $idUser);
                        if (mysqli_stmt_execute($stmtUpdate)) {
                            mysqli_stmt_close($stmtUpdate);

                            // Jika akun yang diedit adalah akun yang sedang login, update session
                            if ($idUser === (int) $_SESSION['id_user']) {
                                $_SESSION['username'] = $username;
                            }

                            header('Location: index.php?status=diedit');
                            exit;
                        }
                        $error = 'Gagal memperbarui data admin.';
                        mysqli_stmt_close($stmtUpdate);
                    }
                } else {
                    // Update username saja
                    $stmtUpdate = mysqli_prepare(
                        $conn,
                        "UPDATE users SET username = ? WHERE id_user = ?"
                    );
                    mysqli_stmt_bind_param($stmtUpdate, "si", $username, $idUser);
                    if (mysqli_stmt_execute($stmtUpdate)) {
                        mysqli_stmt_close($stmtUpdate);

                        if ($idUser === (int) $_SESSION['id_user']) {
                            $_SESSION['username'] = $username;
                        }

                        header('Location: index.php?status=diedit');
                        exit;
                    }
                    $error = 'Gagal memperbarui username admin.';
                    mysqli_stmt_close($stmtUpdate);
                }
            }
        }
    }

    $pageTitle = 'Edit Admin';
    require_once __DIR__ . '/../assets/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Edit Admin</h4>
    <p class="text-secondary mb-0">
        Perbarui data akun admin atau perbarui password.
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
                <label class="form-label">ID Admin</label>
                <input
                    type="text"
                    class="form-control"
                    value="#<?php echo htmlspecialchars($admin['id_user'], ENT_QUOTES, 'UTF-8'); ?>"
                    disabled
                >
                <div class="form-text">
                    ID admin diatur otomatis oleh sistem.
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">
                    Username <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-control"
                    maxlength="50"
                    value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                    required
                    autocomplete="username"
                >
                <div class="form-text">
                    Gunakan 3-50 karakter (huruf, angka, atau garis bawah).
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold text-secondary mb-3">Ubah Password (Opsional)</h6>

            <div class="mb-3">
                <label for="password" class="form-label">
                    Password Baru
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    minlength="5"
                    placeholder="Kosongkan jika tidak ingin mengubah password"
                    autocomplete="new-password"
                >
                <div class="form-text">
                    Biarkan kosong jika tetap menggunakan password lama.
                </div>
            </div>

            <div class="mb-4">
                <label for="konfirmasi_password" class="form-label">
                    Konfirmasi Password Baru
                </label>
                <input
                    type="password"
                    name="konfirmasi_password"
                    id="konfirmasi_password"
                    class="form-control"
                    minlength="5"
                    placeholder="Ulangi password baru jika diubah"
                    autocomplete="new-password"
                >
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
