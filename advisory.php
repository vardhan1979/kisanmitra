<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();
$advisories = $pdo ? $pdo->query('SELECT a.*, c.name_en AS crop_name, c.name_te AS crop_te FROM advisories a JOIN crops c ON a.crop_id = c.id')->fetchAll() : [];
?>
<main class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-[#14532D] mb-6"><?= t('section_advisory') ?></h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($advisories as $adv): ?>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-2">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-[#14532D]"><?= e($adv['crop_name']) ?> (<?= e($adv['crop_te']) ?>)</h2>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold"><?= e($adv['season']) ?> • <?= e($adv['soil_type']) ?></span>
                </div>
                <p class="text-xs text-slate-600"><strong>Sowing Window:</strong> <?= e($adv['sowing_window']) ?> | <strong>Seed Rate:</strong> <?= e((string)$adv['seed_rate_kg_per_acre']) ?> kg/acre</p>
                <p class="text-xs text-slate-600"><strong>Recommended NPK:</strong> <?= e($adv['npk_kg_per_acre']) ?> | <strong>Spacing:</strong> <?= e($adv['spacing_cm']) ?></p>
                <p class="text-sm text-slate-700"><strong>Fertilizer Schedule:</strong> <?= e($adv['fertilizer_schedule']) ?></p>
                <p class="text-sm text-slate-700"><strong>Irrigation:</strong> <?= e($adv['irrigation_schedule']) ?></p>
                <p class="text-sm text-emerald-900 bg-emerald-50 p-3 rounded-lg"><strong>Pest & Disease Control:</strong> <?= e($adv['pest_management']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
