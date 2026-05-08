<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Email/NISN dan password wajib diisi.';
    } else {
        $encoded = rawurlencode($identifier);
        $query = 'pendaftaran?select=id,nama_lengkap,nisn,email,password,asal_sekolah,jalur_pendaftaran,status_verifikasi,created_at'
            . '&or=(email.eq.' . $encoded . ',nisn.eq.' . $encoded . ')'
            . '&limit=1';

        $result = supabaseRequest('GET', $query);

        if ($result['ok'] && is_array($result['data']) && count($result['data']) === 1) {
            $student = $result['data'][0];
            if (isset($student['password']) && password_verify($password, (string) $student['password'])) {
                unset($student['password']);
                $_SESSION['student'] = $student;
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Password salah.';
        } else {
            $error = $result['ok'] ? 'Akun tidak ditemukan.' : ('Gagal login: ' . $result['error']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login PPDB</title>
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
    <div class="mx-auto w-full max-w-lg px-4">
        <div class="rounded-2xl border border-emerald-500/10 bg-slate-900 p-8 shadow-xl shadow-emerald-500/10">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="font-title text-2xl font-bold text-white">Login Siswa</h1>
                <a href="index.php" class="text-sm font-semibold text-emerald-300 transition-all duration-300 hover:text-emerald-200">Kembali</a>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mb-4 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label for="identifier" class="mb-1 block text-sm font-semibold text-slate-200">Email atau NISN</label>
                    <input id="identifier" name="identifier" type="text" value="<?= htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold text-slate-200">Password</label>
                    <input id="password" name="password" type="password" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-2 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
                </div>
                <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-4 py-3 font-semibold text-slate-950 transition-all duration-300 hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/10">Login</button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-400">Belum punya akun? <a href="register.php" class="font-semibold text-emerald-300 transition-all duration-300 hover:text-emerald-200">Daftar di sini</a></p>
        </div>
    </div>
</body>
</html>
