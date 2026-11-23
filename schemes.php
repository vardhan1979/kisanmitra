<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();
$schemes = $pdo ? $pdo->query('SELECT * FROM schemes ORDER BY id ASC')->fetchAll() : [];
?>
<main class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-[#14532D] mb-6"><?= t('section_schemes') ?></h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($schemes as $s): ?>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden flex flex-col">
                <img src="<?= e($s['image_path']) ?>" alt="<?= e($s['title_en']) ?>" class="w-full h-48 object-cover">
                <div class="p-6 space-y-3 flex-grow">
                    <span class="px-2.5 py-1 rounded bg-amber-100 text-amber-900 text-xs font-bold"><?= e($s['govt_level']) ?></span>
                    <h2 class="text-lg font-extrabold text-[#14532D]"><?= e($s['title_en']) ?></h2>
                    <div class="text-sm font-bold text-[#16A34A]"><?= e($s['benefit_summary']) ?></div>
                    <p class="text-sm text-slate-600"><?= e($s['description_en']) ?></p>
                    <p class="text-xs text-slate-500"><strong>Documents:</strong> <?= e($s['required_documents']) ?></p>
                    <a href="<?= e($s['official_portal_url']) ?>" target="_blank" rel="noopener" class="inline-block mt-2 text-sm font-bold text-[#14532D] underline">Visit Official Portal →</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
