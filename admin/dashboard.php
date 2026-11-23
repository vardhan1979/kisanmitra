<?php
require_once __DIR__ . '/../config/config.php';
$pdo = get_db();
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    if (($_POST['admin_action'] ?? '') === 'update_mandi' && $pdo) {
        $stmt = $pdo->prepare('INSERT INTO mandi_prices (crop_id, district_id, mandi_name, variety, min_price, max_price, modal_price, reported_date) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())');
        $stmt->execute([
            (int)$_POST['crop_id'],
            (int)$_POST['district_id'],
            trim((string)$_POST['mandi_name']),
            trim((string)$_POST['variety']),
            (float)$_POST['min_price'],
            (float)$_POST['max_price'],
            (float)$_POST['modal_price'],
        ]);
        $flash = 'Daily APMC Mandi Price published successfully.';
    } elseif (($_POST['admin_action'] ?? '') === 'reply_ticket' && $pdo) {
        $stmt = $pdo->prepare('UPDATE pest_tickets SET officer_reply = ?, replied_by = ?, status = "Resolved" WHERE id = ?');
        $stmt->execute([
            trim((string)$_POST['officer_reply']),
            'Dr. K. Srinivasa Rao (RBK Officer)',
            (int)$_POST['ticket_id'],
        ]);
        $flash = 'Pest Ticket resolved and farmer notified.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RBK Agri-Officer & State Admin Dashboard — KisanMitra AP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-6">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-[#14532D] text-white p-6 rounded-2xl flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-extrabold">Rythu Seva Kendram (RBK) Officer & Admin Console</h1>
                <p class="text-xs text-emerald-200">Manage APMC Mandi Rates, Verify Pest Tickets & Publish District Advisories</p>
            </div>
            <a href="../index.php" class="px-4 py-2 rounded-xl bg-white text-[#14532D] font-bold text-sm">← Back to Farmer Portal</a>
        </div>
        <?php if ($flash): ?>
            <div class="p-4 rounded-xl bg-emerald-100 border border-emerald-400 text-emerald-900 font-bold text-sm"><?= e($flash) ?></div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <form method="post" class="bg-white p-6 rounded-2xl shadow-sm border space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="admin_action" value="update_mandi">
                <h2 class="text-lg font-extrabold text-[#14532D]">Publish Daily Mandi Rate (₹/Quintal)</h2>
                <input type="text" name="mandi_name" required placeholder="e.g., Guntur APMC Mirchi Yard" class="w-full border rounded-lg px-3 py-2 text-sm">
                <input type="text" name="variety" required placeholder="e.g., Dry Red Chilli (Teja)" class="w-full border rounded-lg px-3 py-2 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <input type="number" step="0.01" name="min_price" required placeholder="Min ₹" class="border rounded-lg px-3 py-2 text-sm">
                    <input type="number" step="0.01" name="max_price" required placeholder="Max ₹" class="border rounded-lg px-3 py-2 text-sm">
                    <input type="number" step="0.01" name="modal_price" required placeholder="Modal ₹" class="border rounded-lg px-3 py-2 text-sm">
                </div>
                <input type="hidden" name="crop_id" value="1">
                <input type="hidden" name="district_id" value="1">
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#16A34A] text-white font-bold text-sm">Publish Rate</button>
            </form>

            <form method="post" class="bg-white p-6 rounded-2xl shadow-sm border space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="admin_action" value="reply_ticket">
                <h2 class="text-lg font-extrabold text-[#14532D]">Respond to Farmer Pest Ticket</h2>
                <input type="number" name="ticket_id" required placeholder="Ticket ID (e.g., 1)" class="w-full border rounded-lg px-3 py-2 text-sm">
                <textarea name="officer_reply" rows="3" required placeholder="Enter ANGRAU recommended chemical/organic spray dosage..." class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#D97706] text-white font-bold text-sm">Send Officer Resolution</button>
            </form>
        </div>
    </div>
</body>
</html>
