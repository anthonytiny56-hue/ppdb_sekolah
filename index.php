<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$stats = [
    'total' => 0,
    'diterima' => 0,
    'pending' => 0,
    'ditolak' => 0,
    'conversion_rate' => 0,
];
$statsError = '';
$dailyTrendMap = [];

$statsResult = supabaseRequest('GET', 'pendaftaran?select=created_at,status_verifikasi');
if ($statsResult['ok'] && is_array($statsResult['data'])) {
    $stats['total'] = count($statsResult['data']);
    foreach ($statsResult['data'] as $row) {
        $createdAt = (string) ($row['created_at'] ?? '');
        if ($createdAt !== '') {
            $dateKey = substr($createdAt, 0, 10);
            if ($dateKey !== '') {
                $dailyTrendMap[$dateKey] = ($dailyTrendMap[$dateKey] ?? 0) + 1;
            }
        }

        $status = strtolower((string) ($row['status_verifikasi'] ?? ''));
        if ($status === 'diterima') {
            $stats['diterima']++;
        }
        if ($status === 'pending') {
            $stats['pending']++;
        }
        if ($status === 'ditolak') {
            $stats['ditolak']++;
        }
    }

    if ($stats['total'] > 0) {
        $stats['conversion_rate'] = round(($stats['diterima'] / $stats['total']) * 100, 1);
    }
} else {
    $statsError = 'Gagal memuat statistik pendaftaran. ' . ($statsResult['error'] ?? '');
}

ksort($dailyTrendMap);
$dailyLabels = array_keys($dailyTrendMap);
$dailyCounts = array_values($dailyTrendMap);
$statusLabels = ['Diterima', 'Ditolak', 'Pending'];
$statusCounts = [$stats['diterima'], $stats['ditolak'], $stats['pending']];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .font-title { font-family: 'Poppins', sans-serif; }
        .font-body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-body min-h-screen bg-[#020617] text-slate-100">
    <nav class="sticky top-0 z-50 border-b border-emerald-500/10 bg-slate-950/80 backdrop-blur-md">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4">
            <div>
                <p class="font-title text-lg font-bold text-emerald-400">PPDB Sekolah</p>
                <p class="text-xs text-slate-400">Modern Admission Platform</p>
            </div>
            <div class="flex gap-3">
                <a href="login.php" class="rounded-2xl border border-emerald-500/20 px-5 py-2 font-semibold text-emerald-300 transition-all duration-300 hover:border-emerald-400 hover:bg-emerald-500/10 hover:shadow-lg hover:shadow-emerald-500/10">Login</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto w-full max-w-6xl px-4 pb-12 pt-12">
        <section class="rounded-2xl border border-emerald-500/10 bg-[#0f172a] p-8 shadow-2xl shadow-emerald-500/10 md:p-12">
            <p class="mb-4 inline-flex rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-300">Penerimaan Peserta Didik Baru</p>
            <h1 class="font-title max-w-3xl text-4xl font-extrabold leading-tight md:text-5xl">Wujudkan Pendaftaran Siswa yang Cepat, Transparan, dan Profesional</h1>
            <p class="mt-4 max-w-2xl text-slate-300">Satu portal untuk pendaftaran, pemantauan status verifikasi, dan proses seleksi yang terpercaya dengan pengalaman modern.</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="register.php" class="rounded-2xl bg-emerald-600 px-6 py-3 font-semibold text-slate-950 transition-all duration-300 hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/10">Mulai Daftar</a>
            </div>
        </section>

        <?php if ($statsError !== ''): ?>
            <div class="mt-8 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
                <?= htmlspecialchars($statsError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="mt-8 grid gap-4 md:grid-cols-4">
            <article class="rounded-2xl border border-emerald-500/10 bg-[#0f172a] p-6 shadow-lg shadow-emerald-500/10">
                <p class="text-sm text-slate-400">Total Pendaftar</p>
                <p class="mt-2 text-3xl font-bold text-emerald-400"><?= $stats['total'] ?></p>
            </article>
            <article class="rounded-2xl border border-emerald-500/10 bg-[#0f172a] p-6 shadow-lg shadow-emerald-500/10">
                <p class="text-sm text-slate-400">Status Diterima</p>
                <p class="mt-2 text-3xl font-bold text-emerald-400"><?= $stats['diterima'] ?></p>
            </article>
            <article class="rounded-2xl border border-emerald-500/10 bg-[#0f172a] p-6 shadow-lg shadow-emerald-500/10">
                <p class="text-sm text-slate-400">Status Pending</p>
                <p class="mt-2 text-3xl font-bold text-amber-300"><?= $stats['pending'] ?></p>
            </article>
            <article class="rounded-2xl border border-emerald-500/10 bg-[#0f172a] p-6 shadow-lg shadow-emerald-500/10">
                <p class="text-sm text-slate-400">Conversion Rate</p>
                <p class="mt-2 text-3xl font-bold text-emerald-300"><?= number_format($stats['conversion_rate'], 1) ?>%</p>
            </article>
        </section>

        <section class="mt-8 grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-emerald-500/10 bg-[#0f172a] p-6 shadow-lg shadow-emerald-500/10">
                <h2 class="font-title text-xl font-bold text-white">Tren Pendaftar Harian</h2>
                <p class="mt-1 text-sm text-slate-400">Naik-turun jumlah pendaftar berdasarkan tanggal registrasi.</p>
                <div class="mt-5 h-72">
                    <canvas id="dailyTrendChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-emerald-500/10 bg-[#0f172a] p-6 shadow-lg shadow-emerald-500/10">
                <h2 class="font-title text-xl font-bold text-white">Komposisi Status Verifikasi</h2>
                <p class="mt-1 text-sm text-slate-400">Perbandingan Diterima, Ditolak, dan Pending saat ini.</p>
                <div class="mt-5 h-72">
                    <canvas id="statusDoughnutChart"></canvas>
                </div>
            </article>
        </section>
    </main>

    <script>
        const dailyLabels = <?= json_encode($dailyLabels) ?>;
        const dailyCounts = <?= json_encode($dailyCounts) ?>;
        const statusLabels = <?= json_encode($statusLabels) ?>;
        const statusCounts = <?= json_encode($statusCounts) ?>;

        const axisColor = '#94a3b8';
        const gridColor = 'rgba(16, 185, 129, 0.12)';

        new Chart(document.getElementById('dailyTrendChart'), {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Jumlah Pendaftar',
                    data: dailyCounts,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 2000 },
                plugins: { legend: { labels: { color: '#e2e8f0' } } },
                scales: {
                    x: { ticks: { color: axisColor }, grid: { color: gridColor } },
                    y: { beginAtZero: true, ticks: { color: axisColor }, grid: { color: gridColor } }
                }
            }
        });

        new Chart(document.getElementById('statusDoughnutChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                    borderColor: '#0f172a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 2000 },
                plugins: { legend: { labels: { color: '#e2e8f0' } } }
            }
        });
    </script>
</body>
</html>
