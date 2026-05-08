<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$errors = [];
$success = '';
$old = [
    'nama_lengkap' => '',
    'nisn' => '',
    'email' => '',
    'asal_sekolah' => '',
    'jalur_pendaftaran' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nama_lengkap'] = trim($_POST['nama_lengkap'] ?? '');
    $old['nisn'] = trim($_POST['nisn'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['asal_sekolah'] = trim($_POST['asal_sekolah'] ?? '');
    $old['jalur_pendaftaran'] = trim($_POST['jalur_pendaftaran'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($old['nama_lengkap'] === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if ($old['nisn'] === '') {
        $errors[] = 'NISN wajib diisi.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($old['asal_sekolah'] === '') {
        $errors[] = 'Asal sekolah wajib diisi.';
    }
    if ($old['jalur_pendaftaran'] === '') {
        $errors[] = 'Jalur pendaftaran wajib dipilih.';
    }

    if (empty($errors)) {
        $payload = [
            'nama_lengkap' => $old['nama_lengkap'],
            'nisn' => $old['nisn'],
            'email' => strtolower($old['email']),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'asal_sekolah' => $old['asal_sekolah'],
            'jalur_pendaftaran' => $old['jalur_pendaftaran'],
            'status_verifikasi' => 'Pending',
        ];

        $result = supabaseRequest(
            'POST',
            'pendaftaran',
            $payload,
            ['Prefer: return=representation']
        );

        if ($result['ok']) {
            $success = 'Pendaftaran berhasil. Silakan login untuk melihat status verifikasi.';
            $old = [
                'nama_lengkap' => '',
                'nisn' => '',
                'email' => '',
                'asal_sekolah' => '',
                'jalur_pendaftaran' => '',
            ];
        } else {
            $errors[] = 'Gagal menyimpan data: ' . $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar PPDB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-title { font-family: 'Poppins', sans-serif; }
        .font-body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-body min-h-screen bg-slate-950 py-10 text-slate-100">
    <div class="mx-auto w-full max-w-2xl px-4">
        <div class="rounded-2xl border border-emerald-500/10 bg-slate-900 p-8 shadow-xl shadow-emerald-500/10">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="font-title text-2xl font-bold text-white">Form Pendaftaran PPDB</h1>
                <a href="index.php" class="text-sm font-semibold text-emerald-300 transition-all duration-300 hover:text-emerald-200">Kembali</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="mb-4 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="mb-4 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-200"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label for="nama_lengkap" class="mb-1 block text-sm font-semibold text-slate-200">Nama Lengkap</label>
                    <input id="nama_lengkap" name="nama_lengkap" type="text" value="<?= htmlspecialchars($old['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                </div>

                <div>
                    <label for="nisn" class="mb-1 block text-sm font-semibold text-slate-200">NISN</label>
                    <input id="nisn" name="nisn" type="text" value="<?= htmlspecialchars($old['nisn'], ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold text-slate-200">Email</label>
                    <input id="email" name="email" type="email" value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold text-slate-200">Password</label>
                    <input id="password" name="password" type="password" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                </div>

                <div>
                    <label for="asal_sekolah" class="mb-1 block text-sm font-semibold text-slate-200">Asal Sekolah</label>
                    <input id="asal_sekolah" name="asal_sekolah" type="text" value="<?= htmlspecialchars($old['asal_sekolah'], ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                </div>

                <div>
                    <label for="jalur_pendaftaran" class="mb-1 block text-sm font-semibold text-slate-200">Jalur Pendaftaran</label>
                    <select id="jalur_pendaftaran" name="jalur_pendaftaran" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                        <option value="">Pilih jalur</option>
                        <?php
                        $jalurOptions = ['Zonasi', 'Prestasi', 'Afirmasi'];
                        foreach ($jalurOptions as $option):
                        ?>
                            <option value="<?= $option ?>" <?= $old['jalur_pendaftaran'] === $option ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $option)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-4 py-3 font-semibold text-slate-950 transition-all duration-300 hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/10">Daftar</button>
            </form>
        </div>
    </div>
</body>
</html>
