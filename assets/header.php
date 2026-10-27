
<?php
    $pageTitle = $pageTitle ?? 'Dashboard';
    $username  = $_SESSION['username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
        | Inventaris Sekolah
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f7fb;
            color: #243047;
            margin: 0;
        }

        .sidebar {
            width: 250px;
            min-width: 250px;
            min-height: 100vh;
            background: #17243a;
            color: #fff;
            display: flex;
            flex-direction: column;
        }

        .brand {
            padding: 25px 20px;
            border-bottom: 1px solid #344158;
        }

        .sidebar .nav-link {
            color: #bdc8da;
            padding: 12px 14px;
            margin-bottom: 5px;
            border-radius: 8px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #2d4263;
            color: #fff;
        }

        .menu-icon {
            display: inline-block;
            width: 25px;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid #344158;
        }

        .main-content {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e8edf4;
            padding: 18px 28px;
        }

        .content-area {
            padding: 28px;
            min-height: calc(100vh - 135px);
        }

        .summary-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 3px 15px rgba(25, 40, 70, .05);
        }

        @media (max-width: 767px) {
            .app-layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-width: 0;
                min-height: auto;
            }

            .sidebar-bottom {
                margin-top: 10px;
            }

            .content-area {
                padding: 18px;
            }

            .topbar {
                padding: 16px 18px;
            }
        }
    </style>
</head>

<body>
<div class="app-layout d-flex">

    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar d-flex justify-content-between
                       align-items-center gap-3">
            <div>
                <h5 class="mb-1 fw-bold">
                    <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
                </h5>
                <small class="text-secondary">
                    Sistem Inventaris Barang Sekolah
                </small>
            </div>

            <div class="text-end">
                <div class="fw-semibold">
                    <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <small class="text-secondary">Pengguna</small>
            </div>
        </header>

        <main class="content-area">
