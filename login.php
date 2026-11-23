<?php
require_once __DIR__ . '/includes/header.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $err = 'Invalid CSRF token.';
    } else {
        $phone = trim((string)($_POST['phone'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');
        $pdo = get_db();
        if ($pdo) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE phone = ? LIMIT 1');
            $stmt->execute([$phone]);
            $u = $stmt->fetch();
            if ($u && password_verify($pass, $u['password_hash'])) {
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['user_name'] = $u['full_name'];
                $_SESSION['user_role'] = $u['role'];
                header('Location: admin/dashboard.php');
                exit;
            }
        }
        $err = 'Invalid phone number or password.';
    }
}
?>
<main class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl p-6 shadow border">
        <h1 class="text-xl font-extrabold text-[#14532D] mb-4">Farmer & RBK Officer Login</h1>
        <?php if ($err): ?><div class="p-3 mb-3 rounded bg-red-50 text-red-700 text-xs font-bold"><?= e($err) ?></div><?php endif; ?>
        <form method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-bold mb-1">Registered Mobile Number</label>
                <input type="text" name="phone" required placeholder="9440198765" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-[#14532D] text-white font-bold text-sm">Sign In</button>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
