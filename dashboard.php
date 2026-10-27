
<?php
    require_once __DIR__ . "/auth.php";

    $resultBarang = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM barang"
    );
    $totalBarang = (int) mysqli_fetch_assoc($resultBarang)['total'];

    $resultKategori = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM kategori"
    );
    $totalKategori = (int) mysqli_fetch_assoc($resultKategori)['total'];

    $resultStok = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(jumlah), 0) AS total FROM barang"
    );
    $totalStok = (int) mysqli_fetch_assoc($resultStok)['total'];

    $resultKondisi = mysqli_query(
    $conn,
    "SELECT kondisi, COUNT(*) AS total
     FROM barang
     GROUP BY kondisi"
    );

    $kondisiBarang = [
    'Baik'         => 0,
    'Rusak Ringan' => 0,
    'Rusak Berat'  => 0,
    ];

    while ($row = mysqli_fetch_assoc($resultKondisi)) {
    $kondisiBarang[$row['kondisi']] = (int) $row['total'];
    }

    $pageTitle = "Dashboard";
    require_once __DIR__ . "/assets/header.php";
?>

<div class="mb-4">
    <h4 class="fw-bold">Ringkasan Inventaris</h4>
    <p class="text-secondary mb-0">
        Pantau data barang dan kondisi inventaris sekolah.
    </p>
</div>

<div class="row g-4 mb-4">

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card summary-card h-100">
            <div class="card-body p-4">
                <div class="text-secondary mb-2">Jenis Barang</div>
                <h2 class="fw-bold mb-2"><?php echo $totalBarang ?></h2>
                <small class="text-secondary">
                    Barang yang tercatat
                </small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card summary-card h-100">
            <div class="card-body p-4">
                <div class="text-secondary mb-2">Total Kategori</div>
                <h2 class="fw-bold mb-2"><?php echo $totalKategori ?></h2>
                <small class="text-secondary">
                    Kelompok barang
                </small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card summary-card h-100">
            <div class="card-body p-4">
                <div class="text-secondary mb-2">Total Unit</div>
                <h2 class="fw-bold mb-2"><?php echo $totalStok ?></h2>
                <small class="text-secondary">
                    Jumlah seluruh barang
                </small>
            </div>
        </div>
    </div>

</div>

<div class="card summary-card mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4">Kondisi Barang</h5>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-success-subtle">
                    <div class="text-success">Baik</div>
                    <h3 class="fw-bold mb-0">
                        <?php echo $kondisiBarang['Baik'] ?>
                    </h3>
                    <small class="text-secondary">
                        Jenis barang
                    </small>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-warning-subtle">
                    <div class="text-warning-emphasis">Rusak Ringan</div>
                    <h3 class="fw-bold mb-0">
                        <?php echo $kondisiBarang['Rusak Ringan'] ?>
                    </h3>
                    <small class="text-secondary">
                        Jenis barang
                    </small>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-danger-subtle">
                    <div class="text-danger">Rusak Berat</div>
                    <h3 class="fw-bold mb-0">
                        <?php echo $kondisiBarang['Rusak Berat'] ?>
                    </h3>
                    <small class="text-secondary">
                        Jenis barang
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card summary-card">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-2">Selamat Datang!</h5>
        <p class="text-secondary mb-3">
            Kelola data inventaris sekolah melalui menu di samping.
            Kamu dapat menambahkan kategori dan mencatat barang
            beserta jumlah serta kondisinya.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="/SerkomSMK/barang/index.php"
               class="btn btn-primary">
                Lihat Data Barang
            </a>

            <a href="/SerkomSMK/kategori/index.php"
               class="btn btn-outline-primary">
                Kelola Kategori
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/assets/footer.php"; ?>
