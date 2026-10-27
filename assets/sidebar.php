<?php
    $currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="brand">
        <div class="fw-bold">Inventaris Sekolah</div>
        <small class="text-white-50">Sistem Pendataan Barang</small>
    </div>

    <div class="px-3 pt-4 pb-2 small text-uppercase text-white-50">
        Menu Utama
    </div>

    <nav class="nav flex-column px-3">
        <a
            href="/SerkomSMK/dashboard.php"
            class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : '' ?>"
        >
            <span class="menu-icon">▦</span> Dashboard
        </a>

        <a
            href="/SerkomSMK/kategori/index.php"
            class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], '/kategori/') ? 'active' : '' ?>"
        >
            <span class="menu-icon">▤</span> Kategori
        </a>

        <a
            href="/SerkomSMK/barang/index.php"
            class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], '/barang/') ? 'active' : '' ?>"
        >
            <span class="menu-icon">▣</span> Data Barang
        </a>


    </nav>

    <div class="sidebar-bottom">
        <a href="/SerkomSMK/logout.php" class="nav-link text-warning">
            <span class="menu-icon">↪</span> Logout
        </a>
    </div>
</aside>
