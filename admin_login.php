<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (isAdminLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password admin wajib diisi.';
    } elseif ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Kredensial admin tidak valid.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login PPDB</title>
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
    <div class="mx-auto w-full max-w-lg rounded-2xl border border-emerald-500/10 bg-slate-900 p-8 shadow-xl shadow-emerald-500/10">
        <div class="mb-6">
            <p class="text-sm text-emerald-300">Panel Admin</p>
            <h1 class="font-title text-3xl font-bold">Login Verifikator PPDB</h1>
        </div>

        <?php if ($error !== ''): ?>
            <div class="mb-4 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label for="username" class="mb-1 block text-sm font-medium text-slate-300">Username</label>
                <input id="username" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-3 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-300">Password</label>
                <input id="password" name="password" type="password" class="w-full rounded-2xl border border-slate-600 bg-slate-800 px-4 py-3 text-white outline-none transition-all duration-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30" required>
            </div>
            <button type="submit" class="w-full rounded-2xl bg-emerald-500 px-4 py-3 font-semibold text-slate-950 transition-all duration-300 hover:bg-emerald-400 hover:shadow-lg hover:shadow-emerald-500/10">Masuk Admin</button>
        </form>

        <a href="index.php" class="mt-5 inline-block text-sm text-slate-400 transition-all duration-300 hover:text-emerald-300">Kembali ke beranda</a>
    </div>
</body>
</html>
