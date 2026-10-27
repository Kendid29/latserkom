
<?php
    require_once "koneksi.php";

    if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
    }

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id_user, username, password
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }

        mysqli_stmt_close($stmt);
    }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Inventaris Sekolah</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<div class="container min-vh-100 d-flex
            align-items-center justify-content-center">
    <div class="card shadow-sm border-0 p-4"
         style="width: 100%; max-width: 420px;">

        <div class="text-center mb-4">
            <h3 class="fw-bold">Inventaris Sekolah</h3>
            <p class="text-secondary">
                Silakan login untuk melanjutkan
            </p>
        </div>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">
                    Username
                </label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-control"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">
                    Password
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Login
            </button>
        </form>

    </div>
</div>

</body>
</html>
