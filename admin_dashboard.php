<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
requireAdminLogin();

$message = $_SESSION['admin_flash_success'] ?? '';
unset($_SESSION['admin_flash_success']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $action = trim($_POST['action'] ?? '');
    $statusTarget = '';

    if ($action === 'verify') {
        $statusTarget = 'Diterima';
    } elseif ($action === 'reject') {
        $statusTarget = 'Ditolak';
    }

    if ($id === '' || $statusTarget === '') {
        $error = 'Aksi tidak valid.';
    } else {
        $updateResult = supabaseRequest(
            'PATCH',
            'pendaftaran?id=eq.' . rawurlencode($id),
            ['status_verifikasi' => $statusTarget],
            ['Prefer: return=representation']
        );

        $updatedRows = is_array($updateResult['data']) ? count($updateResult['data']) : 0;

        if ($updateResult['ok'] && $updatedRows > 0) {
            $_SESSION['admin_flash_success'] = 'Status pendaftaran berhasil diperbarui menjadi ' . $statusTarget . '.';
            header('Refresh:0');
            exit;
        } else {
            $error = 'Gagal memperbarui status. Pastikan filter primary key valid (id=eq.<id>) dan policy UPDATE mengizinkan aksi ini.';
            if (!$updateResult['ok']) {
                $error .= ' Detail: ' . ($updateResult['error'] ?? 'Unknown error.');
            }
        }
    }
}

$rowsResult = supabaseRequest(
    'GET',
    'pendaftaran?select=id,nama_lengkap,nisn,asal_sekolah,jalur_pendaftaran,created_at,status_verifikasi&order=created_at.desc'
);
$rows = [];
if ($rowsResult['ok'] && is_array($rowsResult['data'])) {
    $rows = $rowsResult['data'];
} else {
    $error = 'Gagal memuat data pendaftaran: ' . ($rowsResult['error'] ?? '');
}

function adminStatusClass(string $status): string
{
    $normalized = strtolower($status);
    if ($normalized === 'diterima') {
        return 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
    }
    if ($normalized === 'ditolak') {
        return 'bg-red-500/20 text-red-300 border-red-500/30';
    }

    return 'bg-amber-500/20 text-amber-300 border-amber-500/30';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard PPDB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-title { font-family: 'Poppins', sans-serif; }
        .font-body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-body min-h-screen bg-[#020617] text-slate-100">
    <header class="sticky top-0 z-40 border-b border-emerald-500/10 bg-slate-950/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
            <div>
                <h1 class="font-title text-xl font-bold text-emerald-300">Admin Dashboard PPDB</h1>
                <p class="text-xs text-slate-400">Kelola verifikasi pendaftaran siswa</p>
            </div>
            <a href="logout.php" class="rounded-2xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 transition-all duration-300 hover:border-emerald-400 hover:bg-emerald-500/10 hover:shadow-lg hover:shadow-emerald-500/10">Logout</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
        <?php if ($message !== ''): ?>
            <div class="mb-5 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-200">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="mb-5 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="overflow-hidden rounded-2xl border border-emerald-500/10 bg-[#0f172a] shadow-xl shadow-emerald-500/10">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-900/80">
                        <tr class="text-left text-sm text-slate-300">
                            <th class="px-4 py-4 font-semibold">Nama</th>
                            <th class="px-4 py-4 font-semibold">NISN</th>
                            <th class="px-4 py-4 font-semibold">Asal Sekolah</th>
                            <th class="px-4 py-4 font-semibold">Jalur</th>
                            <th class="px-4 py-4 font-semibold">Tanggal</th>
                            <th class="px-4 py-4 font-semibold">Status</th>
                            <th class="px-4 py-4 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm">
                        <?php foreach ($rows as $row): ?>
                            <?php $status = (string) ($row['status_verifikasi'] ?? 'Pending'); ?>
                            <tr class="transition-all duration-300 hover:bg-slate-700/30">
                                <td class="px-4 py-4"><?= htmlspecialchars((string) ($row['nama_lengkap'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-4"><?= htmlspecialchars((string) ($row['nisn'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-4"><?= htmlspecialchars((string) ($row['asal_sekolah'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-4"><?= htmlspecialchars((string) ($row['jalur_pendaftaran'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-4"><?= htmlspecialchars(formatTanggalIndonesia((string) ($row['created_at'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-4">
                                    <span class="rounded-2xl border px-3 py-1 text-xs font-semibold <?= adminStatusClass($status) ?>">
                                        <?= htmlspecialchars(ucfirst(strtolower($status)), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <form method="post">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($row['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="verify">
                                            <button type="submit" class="rounded-2xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-slate-950 transition-all duration-300 hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/10">Verify</button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($row['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="rounded-2xl bg-red-500 px-3 py-2 text-xs font-semibold text-white transition-all duration-300 hover:bg-red-400">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
