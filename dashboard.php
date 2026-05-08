<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
requireLogin();

$student = $_SESSION['student'];
$id = rawurlencode((string) ($student['id'] ?? ''));
$error = '';

if ($id !== '') {
    $result = supabaseRequest(
        'GET',
        'pendaftaran?select=id,nama_lengkap,nisn,email,asal_sekolah,jalur_pendaftaran,status_verifikasi,created_at&id=eq.' . $id . '&limit=1'
    );

    if ($result['ok'] && is_array($result['data']) && count($result['data']) === 1) {
        $_SESSION['student'] = $result['data'][0];
        $student = $_SESSION['student'];
    } elseif (!$result['ok']) {
        $error = 'Gagal memuat data terbaru: ' . ($result['error'] ?? '');
    }
}

$status = strtolower((string) ($student['status_verifikasi'] ?? 'pending'));
$statusLabel = ucfirst($status);
$statusClass = 'bg-amber-500/20 text-amber-300 border-amber-500/30';
if ($status === 'diterima') {
    $statusClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
} elseif ($status === 'ditolak') {
    $statusClass = 'bg-red-500/20 text-red-300 border-red-500/30';
}

$createdAt = (string) ($student['created_at'] ?? '-');
$formattedCreatedAt = '-';
if ($createdAt !== '-') {
    try {
        $dt = new DateTime($createdAt);
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $monthNumber = (int) $dt->format('n');
        $formattedCreatedAt = $dt->format('d') . ' ' . ($bulan[$monthNumber] ?? $dt->format('F')) . $dt->format(' Y');
    } catch (Exception $exception) {
        $formattedCreatedAt = $createdAt;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Member PPDB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-title { font-family: 'Poppins', sans-serif; }
        .font-body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-body min-h-screen bg-slate-950 px-4 py-10 text-slate-100">
    <div class="mx-auto w-full max-w-4xl">
        <?php if ($error !== ''): ?>
            <div class="mb-4 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="rounded-2xl border border-emerald-500/10 bg-slate-900 p-8 shadow-2xl shadow-emerald-500/10">
            <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-emerald-300">Member Card</p>
                    <h1 class="font-title text-3xl font-bold">Dashboard Siswa PPDB</h1>
                </div>
                <div class="flex gap-3">
                    <a href="index.php" class="rounded-2xl border border-slate-600 px-4 py-2 text-sm font-semibold transition-all duration-300 hover:border-emerald-400 hover:bg-emerald-500/10 hover:shadow-lg hover:shadow-emerald-500/10">Beranda</a>
                    <a href="logout.php" class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-slate-950 transition-all duration-300 hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/10">Logout</a>
                </div>
            </div>

            <div class="mb-6 rounded-2xl border p-5 <?= $statusClass ?>">
                <p class="text-xs uppercase tracking-wide">Status Verifikasi</p>
                <p class="mt-2 text-2xl font-bold"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                    <p class="text-xs uppercase text-slate-400">Nama Lengkap</p>
                    <p class="mt-1 text-lg font-semibold"><?= htmlspecialchars((string) ($student['nama_lengkap'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                    <p class="text-xs uppercase text-slate-400">NISN</p>
                    <p class="mt-1 text-lg font-semibold"><?= htmlspecialchars((string) ($student['nisn'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                    <p class="text-xs uppercase text-slate-400">Email</p>
                    <p class="mt-1 text-lg font-semibold"><?= htmlspecialchars((string) ($student['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                    <p class="text-xs uppercase text-slate-400">Asal Sekolah</p>
                    <p class="mt-1 text-lg font-semibold"><?= htmlspecialchars((string) ($student['asal_sekolah'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                    <p class="text-xs uppercase text-slate-400">Jalur Pendaftaran</p>
                    <p class="mt-1 text-lg font-semibold"><?= htmlspecialchars((string) ($student['jalur_pendaftaran'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                    <p class="text-xs uppercase text-slate-400">Tanggal Daftar</p>
                    <p class="mt-1 text-lg font-semibold"><?= htmlspecialchars($formattedCreatedAt, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
