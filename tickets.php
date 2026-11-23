<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();
$tickets = $pdo ? $pdo->query('SELECT t.*, c.name_en AS crop_name, d.name_en AS district_name FROM pest_tickets t JOIN crops c ON t.crop_id = c.id JOIN districts d ON t.district_id = d.id ORDER BY t.created_at DESC')->fetchAll() : [];
?>
<main class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-[#14532D] mb-6"><?= t('section_tickets') ?></h1>
    <div class="space-y-4">
        <?php foreach ($tickets as $tk): ?>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-bold text-[#14532D]"><?= e($tk['ticket_code']) ?> — <?= e($tk['symptom_title']) ?></span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800"><?= e($tk['status']) ?></span>
                </div>
                <p class="text-sm text-slate-600 mb-2"><?= e($tk['symptom_details']) ?></p>
                <?php if (!empty($tk['officer_reply'])): ?>
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950">
                        <strong>RBK Officer Reply (<?= e($tk['replied_by']) ?>):</strong> <?= e($tk['officer_reply']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
