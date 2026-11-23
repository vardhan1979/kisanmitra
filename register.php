<?php
require_once __DIR__ . '/includes/header.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $name = trim((string)($_POST['full_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $pass = password_hash((string)($_POST['password'] ?? ''), PASSWORD_DEFAULT);
    $districtId = (int)($_POST['district_id'] ?? 1);
    $pdo = get_db();
    if ($pdo && $name !== '' && $phone !== '') {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, phone, password_hash, role, district_id) VALUES (?, ?, ?, "farmer", ?)');
        $stmt->execute([$name, $phone, $pass, $districtId]);
        $msg = 'Farmer account registered! You can now log in.';
    }
}
?>
<main class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl p-6 shadow border">
        <h1 class="text-xl font-extrabold text-[#14532D] mb-4">Farmer Registration (RBK Portal)</h1>
        <?php if ($msg): ?><div class="p-3 mb-3 rounded bg-emerald-50 text-emerald-800 text-xs font-bold"><?= e($msg) ?></div><?php endif; ?>
        <form method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-bold mb-1">Full Name</label>
                <input type="text" name="full_name" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Mobile Number</label>
                <input type="text" name="phone" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-[#16A34A] text-white font-bold text-sm">Register Farmer Account</button>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
