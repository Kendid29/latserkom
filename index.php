
<?php
    require_once __DIR__ . '/koneksi.php';

    // Ringkasan inventaris
    $result = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total_jenis,
        COALESCE(SUM(jumlah), 0) AS total_unit,
        COALESCE(SUM(kondisi = 'Baik'), 0) AS kondisi_baik
     FROM barang"
    );
    $ringkasan = mysqli_fetch_assoc($result);

    // Jumlah kategori
    $result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM kategori"
    );
    $totalKategori = (int) mysqli_fetch_assoc($result)['total'];

    // Data grafik batang: Stok per Kategori
    $grafikKategoriQuery = mysqli_query(
    $conn,
    "SELECT
            k.nama_kategori,
            COALESCE(SUM(b.jumlah), 0) AS total_unit
         FROM kategori k
         LEFT JOIN barang b ON b.id_kategori = k.id_kategori
         GROUP BY k.id_kategori, k.nama_kategori
         ORDER BY total_unit DESC"
    );
    $grafikKategoriLabels = [];
    $grafikKategoriData   = [];
    while ($rowKat = mysqli_fetch_assoc($grafikKategoriQuery)) {
    $grafikKategoriLabels[] = $rowKat['nama_kategori'];
    $grafikKategoriData[]   = (int) $rowKat['total_unit'];
    }

    // Data grafik batang: Kondisi Barang
    $grafikKondisiQuery = mysqli_query(
    $conn,
    "SELECT
            COALESCE(SUM(CASE WHEN kondisi = 'Baik' THEN jumlah ELSE 0 END), 0) AS baik,
            COALESCE(SUM(CASE WHEN kondisi = 'Rusak Ringan' THEN jumlah ELSE 0 END), 0) AS rusak_ringan,
            COALESCE(SUM(CASE WHEN kondisi = 'Rusak Berat' THEN jumlah ELSE 0 END), 0) AS rusak_berat
         FROM barang"
    );
    $kondisiData         = mysqli_fetch_assoc($grafikKondisiQuery);
    $grafikKondisiLabels = ['Baik', 'Rusak Ringan', 'Rusak Berat'];
    $grafikKondisiData   = [
    (int) $kondisiData['baik'],
    (int) $kondisiData['rusak_ringan'],
    (int) $kondisiData['rusak_berat'],
    ];

    // Filter dan pencarian
    $cari           = trim($_GET['cari'] ?? '');
    $kategoriFilter = trim($_GET['kategori'] ?? '');
    $kondisiFilter  = trim($_GET['kondisi'] ?? '');

    $kondisiValid = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    $sql = "SELECT
            b.id_barang,
            b.nama_barang,
            b.jumlah,
            b.satuan,
            b.kondisi,
            b.keterangan,
            k.nama_kategori
        FROM barang b
        INNER JOIN kategori k
            ON b.id_kategori = k.id_kategori
        WHERE 1 = 1";

    $types  = '';
    $params = [];

    if ($cari !== '') {
    $sql     .= " AND (b.nama_barang LIKE ? OR b.id_barang LIKE ?)";
    $keyword  = '%' . $cari . '%';

    $types    .= 'ss';
    $params[]  = $keyword;
    $params[]  = $keyword;
    }

    if ($kategoriFilter !== '') {
    $sql      .= " AND b.id_kategori = ?";
    $types    .= 's';
    $params[]  = $kategoriFilter;
    }

    if (in_array($kondisiFilter, $kondisiValid, true)) {
    $sql      .= " AND b.kondisi = ?";
    $types    .= 's';
    $params[]  = $kondisiFilter;
    }

    $sql .= " ORDER BY b.nama_barang ASC";

    // Pagination: maksimal 5 barang per halaman
    $perPage  = 5;
    $halaman  = max(1, (int) ($_GET['halaman'] ?? 1));

    // Hitung jumlah data sesuai pencarian dan filter
    $countSql = "SELECT COUNT(*) AS total
             FROM barang b
             INNER JOIN kategori k
                 ON b.id_kategori = k.id_kategori
             WHERE 1 = 1";

    $countTypes  = '';
    $countParams = [];

    if ($cari !== '') {
    $countSql .= " AND (b.nama_barang LIKE ? OR b.id_barang LIKE ?)";
    $keyword   = '%' . $cari . '%';

    $countTypes    .= 'ss';
    $countParams[]  = $keyword;
    $countParams[]  = $keyword;
    }

    if ($kategoriFilter !== '') {
    $countSql      .= " AND b.id_kategori = ?";
    $countTypes    .= 's';
    $countParams[]  = $kategoriFilter;
    }

    if (in_array($kondisiFilter, $kondisiValid, true)) {
    $countSql      .= " AND b.kondisi = ?";
    $countTypes    .= 's';
    $countParams[]  = $kondisiFilter;
    }

    $countStmt  = mysqli_prepare($conn, $countSql);

    if ($countTypes !== '') {
    mysqli_stmt_bind_param(
        $countStmt,
        $countTypes,
        ...$countParams
    );
    }

    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $totalData   = (int) mysqli_fetch_assoc($countResult)['total'];

    mysqli_stmt_close($countStmt);

    $totalHalaman = max(1, (int) ceil($totalData / $perPage));

    // Pastikan halaman tidak melebihi halaman terakhir
    $halaman = min($halaman, $totalHalaman);

    $offset = ($halaman - 1) * $perPage;

    // Tambahkan batas data ke query utama
    $sql .= " LIMIT ? OFFSET ?";

    $types    .= 'ii';
    $params[] = $perPage;
    $params[] = $offset;

    $stmt  = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);

    $daftarBarang  = mysqli_stmt_get_result($stmt);

    if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $daftarBarang = mysqli_stmt_get_result($stmt);

    $resultKategori = mysqli_query(
    $conn,
    "SELECT id_kategori, nama_kategori
     FROM kategori
     ORDER BY nama_kategori ASC"
    );
    $daftarKategori = mysqli_fetch_all($resultKategori, MYSQLI_ASSOC);

    function e($value)
    {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Inventaris Sekolah</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f6f8fc;
            color: #243047;
        }

        .navbar {
            background: #fff;
            border-bottom: 1px solid #e8edf4;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #e7efff;
            color: #2457a7;
            font-size: 22px;
        }

        .hero {
            background: linear-gradient(120deg, #17243a, #294b79);
            color: white;
            border-radius: 22px;
            padding: 48px;
        }

        .hero-label {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 30px;
            background: rgba(255,255,255,.12);
            font-size: 13px;
        }

        .stat-card,
        .data-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(30, 50, 80, .05);
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
        }

        .section-title {
            font-weight: 700;
            letter-spacing: -.4px;
        }

        .table > :not(caption) > * > * {
            padding: 15px 18px;
        }

        .footer {
            background: #fff;
            border-top: 1px solid #e8edf4;
        }

        @media (max-width: 767px) {
            .hero {
                padding: 28px 22px;
            }
        }
    </style>
</head>

<body>

<!-- Navigasi publik, tanpa tombol login -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container py-2 d-flex justify-content-between align-items-center">
        <a
            class="navbar-brand d-flex align-items-center gap-3"
            href="index.php"
        >

            <div>
                <div class="fw-bold">Inventaris Sekolah</div>
                <small class="text-secondary">
                    Sistem Informasi Inventaris
                </small>
            </div>
        </a>

        <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['id_user'])): ?>
                <a href="/SerkomSMK/dashboard.php" class="btn btn-primary btn-sm px-3">
                    Dashboard Admin
                </a>
            <?php else: ?>
                <a href="/SerkomSMK/login.php" class="btn btn-outline-primary btn-sm px-3">
                    Login Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4 py-lg-5">

    <!-- Hero section -->
    <section class="hero mb-4 mb-lg-5">
        <span class="hero-label">
            SISTEM INFORMASI SEKOLAH
        </span>

        <h1 class="display-5 fw-bold mt-3 mb-3">
            Data Inventaris<br>Sekolah
        </h1>

        <p class="text-white-50 mb-0" style="max-width: 600px;">
            Lihat informasi inventaris sekolah secara praktis.
            Temukan barang berdasarkan nama, kategori, dan kondisi
            melalui satu halaman.
        </p>
    </section>

    <!-- Ringkasan data: Grafik Batang -->
    <section class="mb-5">
        <div class="row g-4">

            <!-- Grafik Stok per Kategori -->
            <div class="col-12 col-lg-7">
                <div class="card stat-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h2 class="section-title h5 mb-1">
                                    Grafik Stok per Kategori
                                </h2>
                                <p class="text-secondary small mb-0">
                                    Perbandingan total unit barang di setiap kategori
                                </p>
                            </div>
                            <span class="badge rounded-pill text-bg-primary">
                                <?php echo count($grafikKategoriLabels); ?> Kategori
                            </span>
                        </div>
                        <div style="position: relative; height: 300px;">
                            <canvas id="chartKategori"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Kondisi Barang -->
            <div class="col-12 col-lg-5">
                <div class="card stat-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h2 class="section-title h5 mb-1">
                                    Grafik Kondisi Barang
                                </h2>
                                <p class="text-secondary small mb-0">
                                    Status kondisi fisik unit inventaris sekolah
                                </p>
                            </div>
                            <span class="badge rounded-pill text-bg-success">
                                <?php echo (int) $kondisiData['baik']; ?> Unit Baik
                            </span>
                        </div>
                        <div style="position: relative; height: 300px;">
                            <canvas id="chartKondisi"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Daftar inventaris -->
    <section id="inventaris">
        <div class="mb-4">
            <h2 class="section-title h3 mb-2">
                Daftar Inventaris
            </h2>
            <p class="text-secondary mb-0">
                Cari dan lihat data barang yang tersedia.
            </p>
        </div>

        <!-- Pencarian dan filter -->
        <div class="card data-card mb-4">
            <div class="card-body p-3 p-lg-4">
                <form method="GET" action="index.php#inventaris">
                    <div class="row g-3">

                        <div class="col-12 col-lg-5">
                            <label for="cari" class="form-label">
                                Cari barang
                            </label>
                            <input
                                type="search"
                                name="cari"
                                id="cari"
                                class="form-control"
                                placeholder="Nama atau ID barang..."
                                value="<?php echo e($cari) ?>"
                            >
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="kategori" class="form-label">
                                Kategori
                            </label>
                            <select
                                name="kategori"
                                id="kategori"
                                class="form-select"
                            >
                                <option value="">Semua kategori</option>

                                <?php foreach ($daftarKategori as $kat): ?>
                                    <option
                                        value="<?php echo e($kat['id_kategori']) ?>"
                                        <?php echo $kategoriFilter === $kat['id_kategori'] ? 'selected' : '' ?>
                                    >
                                        <?php echo e($kat['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label for="kondisi" class="form-label">
                                Kondisi
                            </label>
                            <select
                                name="kondisi"
                                id="kondisi"
                                class="form-select"
                            >
                                <option value="">Semua kondisi</option>

                                <?php foreach (['Baik', 'Rusak Ringan', 'Rusak Berat'] as $k): ?>
                                    <option
                                        value="<?php echo e($k) ?>"
                                        <?php echo $kondisiFilter === $k ? 'selected' : '' ?>
                                    >
                                        <?php echo e($k) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-lg-2 d-flex align-items-end gap-2">
                            <button class="btn btn-primary flex-grow-1">
                                Cari
                            </button>
                            <a
                                href="index.php#inventaris"
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel barang -->
        <div class="card data-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID Barang</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Kondisi</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (mysqli_num_rows($daftarBarang) > 0): ?>
                            <?php while ($barang = mysqli_fetch_assoc($daftarBarang)): ?>
                                <?php
                                    $badge = match ($barang['kondisi']) {
                                        'Baik'         => 'text-bg-success',
                                        'Rusak Ringan' => 'text-bg-warning',
                                        default        => 'text-bg-danger'
                                    };
                                ?>

                                <tr>
                                    <td>
                                        <span class="badge text-bg-primary">
                                            <?php echo e($barang['id_barang']) ?>
                                        </span>
                                    </td>

                                    <td class="fw-semibold">
                                        <?php echo e($barang['nama_barang']) ?>
                                    </td>

                                    <td>
                                        <?php echo e($barang['nama_kategori']) ?>
                                    </td>

                                    <td>
                                        <?php echo (int) $barang['jumlah'] . ' ' . e($barang['satuan'] ?? 'unit') ?>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo $badge ?>">
                                            <?php echo e($barang['kondisi']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td
                                    colspan="5"
                                    class="text-center py-5 text-secondary"
                                >
                                    Data barang tidak ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


<div class="card-footer bg-white border-0 p-3">
    <?php
        $mulaiData = $totalData > 0 ? $offset + 1 : 0;
        $akhirData = min($offset + $perPage, $totalData);

        // Pertahankan pencarian dan filter saat berpindah halaman
        $queryPagination = [
            'cari'     => $cari,
            'kategori' => $kategoriFilter,
            'kondisi'  => $kondisiFilter,
        ];
    ?>

    <div class="d-flex flex-column flex-md-row
                justify-content-between align-items-center gap-3">

        <small class="text-secondary">
            Menampilkan <?php echo $mulaiData ?>–<?php echo $akhirData ?>
            dari <?php echo $totalData ?> data barang
        </small>

        <?php if ($totalHalaman > 1): ?>
            <nav aria-label="Navigasi halaman barang">
                <ul class="pagination pagination-sm mb-0">

                    <!-- Tombol sebelumnya -->
                    <li class="page-item <?php echo $halaman <= 1 ? 'disabled' : '' ?>">
                        <?php $queryPagination['halaman'] = max(1, $halaman - 1); ?>

                        <a
                            class="page-link"
                            href="?<?php echo htmlspecialchars(http_build_query($queryPagination), ENT_QUOTES, 'UTF-8') ?>#inventaris"
                            aria-label="Halaman sebelumnya"
                        >
                            &laquo;
                        </a>
                    </li>

                    <!-- Nomor halaman -->
                    <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                        <?php $queryPagination['halaman'] = $i; ?>

                        <li class="page-item <?php echo $i === $halaman ? 'active' : '' ?>">
                            <a
                                class="page-link"
                                href="?<?php echo htmlspecialchars(http_build_query($queryPagination), ENT_QUOTES, 'UTF-8') ?>#inventaris"
                                <?php echo $i === $halaman ? 'aria-current="page"' : '' ?>
                            >
                                <?php echo $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Tombol berikutnya -->
                    <li class="page-item <?php echo $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                        <?php $queryPagination['halaman'] = min($totalHalaman, $halaman + 1); ?>

                        <a
                            class="page-link"
                            href="?<?php echo htmlspecialchars(http_build_query($queryPagination), ENT_QUOTES, 'UTF-8') ?>#inventaris"
                            aria-label="Halaman berikutnya"
                        >
                            &raquo;
                        </a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>

        </div>
    </section>

</main>

<footer class="footer py-4 mt-4">
    <div class="container text-center">
        <div class="fw-semibold">Inventaris Sekolah</div>
        <small class="text-secondary">
            &copy; <?php echo date('Y') ?> Sistem Informasi Inventaris Sekolah
        </small>
    </div>
</footer>

<script src="/SerkomSMK/assets/chart.umd.min.js"></script>
<script>
if (typeof Chart === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>');
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Grafik Batang: Stok per Kategori
    const ctxKategori = document.getElementById('chartKategori');
    if (ctxKategori) {
        new Chart(ctxKategori, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($grafikKategoriLabels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    label: 'Total Unit',
                    data: <?php echo json_encode($grafikKategoriData); ?>,
                    backgroundColor: 'rgba(36, 87, 167, 0.85)',
                    hoverBackgroundColor: '#1d427d',
                    borderColor: '#2457a7',
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.parsed.y + ' unit barang';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#eef2f7'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // 2. Grafik Batang: Kondisi Barang
    const ctxKondisi = document.getElementById('chartKondisi');
    if (ctxKondisi) {
        new Chart(ctxKondisi, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($grafikKondisiLabels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    label: 'Total Unit',
                    data: <?php echo json_encode($grafikKondisiData); ?>,
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.85)',
                        'rgba(255, 193, 7, 0.85)',
                        'rgba(220, 53, 69, 0.85)'
                    ],
                    hoverBackgroundColor: [
                        '#146c43',
                        '#d39e00',
                        '#b02a37'
                    ],
                    borderColor: [
                        '#198754',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 54
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.parsed.y + ' unit (' + context.label + ')';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#eef2f7'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
</body>
</html>

<?php
    mysqli_stmt_close($stmt);
?>
