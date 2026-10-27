
<?php
    require_once "koneksi.php";

    $username     = "admin3";
    $passwordBaru = "admin333";

    $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
    $conn,
    "UPDATE users SET password = ? WHERE username = ?"
    );

    mysqli_stmt_bind_param($stmt, "ss", $hash, $username);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    echo "Password berhasil diperbarui menjadi hash.";
    } else {
    echo "Password tidak diperbarui. Periksa username dan database.";
    }

    mysqli_stmt_close($stmt);
?>
