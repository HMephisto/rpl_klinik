<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan kata sandi harus diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Pengguna WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Cek password (bisa untuk plain text atau yang sudah di hash)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                
                header("Location: index.php");
                exit;
            } else {
                $error = 'Kata sandi salah.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Klinik</title>
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-5xl w-full bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Side: Image & Branding -->
        <div class="w-full md:w-1/2 relative min-h-[300px] md:min-h-full flex-shrink-0">
            <img src="assets/img/image.png" alt="Clinic Interior" class="absolute inset-0 w-full h-full object-cover">
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent"></div>
            
            <!-- Branding Text -->
            <div class="absolute bottom-0 left-0 p-8 md:p-10 w-full text-white">
                <div class="flex items-center space-x-3 mb-4">
                    <!-- Clinic Icon -->
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h2 class="text-3xl font-bold tracking-wide">Klinik </h2>
                </div>
                <p class="text-slate-200 text-lg leading-relaxed">
                    Sistem Manajemen Klinik Terpadu. Profesional, presisi, dan andal.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            
            <div class="mb-8">
                <h1 class="text-4xl font-extrabold text-slate-900 mb-2 tracking-tight">Selamat Datang</h1>
                <p class="text-slate-500">Silakan masuk ke Sistem Klinik </p>
                
                <?php if ($error): ?>
                <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm flex items-center">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <form action="login.php" method="POST" class="space-y-6">

                <!-- Input Fields -->
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-500 mb-2">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-slate-700 placeholder-slate-400" required>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-500">Kata Sandi</label>
                        <!-- <a href="#" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lupa Kata Sandi?</a> -->
                    </div>
                    <input type="password" id="password" name="password" placeholder="••••••••" class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-slate-700 placeholder-slate-400" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-8 shadow-md">
                    Login
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

        </div>
    </div>
</body>
</html>
