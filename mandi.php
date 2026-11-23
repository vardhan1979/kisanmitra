<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();
$prices = $pdo ? $pdo->query('SELECT m.*, c.name_en AS crop_name, d.name_en AS district_name FROM mandi_prices m JOIN crops c ON m.crop_id = c.id JOIN districts d ON m.district_id = d.id ORDER BY m.reported_date DESC')->fetchAll() : [];
?>
<main class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-[#14532D] mb-6"><?= t('section_mandi') ?></h1>
    <div class="bg-white rounded-2xl shadow-sm border overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-[#14532D] text-white">
                <tr>
                    <th class="p-3.5">Crop</th>
                    <th class="p-3.5">APMC Mandi</th>
                    <th class="p-3.5">Variety</th>
                    <th class="p-3.5 text-right">Min (₹/Qtl)</th>
                    <th class="p-3.5 text-right">Max (₹/Qtl)</th>
                    <th class="p-3.5 text-right">Modal (₹/Qtl)</th>
                    <th class="p-3.5">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($prices as $row): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3.5 font-bold text-[#14532D]"><?= e($row['crop_name']) ?></td>
                        <td class="p-3.5"><?= e($row['mandi_name']) ?></td>
                        <td class="p-3.5"><?= e($row['variety']) ?></td>
                        <td class="p-3.5 text-right">₹<?= number_format((float)$row['min_price']) ?></td>
                        <td class="p-3.5 text-right">₹<?= number_format((float)$row['max_price']) ?></td>
                        <td class="p-3.5 text-right font-extrabold text-[#16A34A]">₹<?= number_format((float)$row['modal_price']) ?></td>
                        <td class="p-3.5"><?= e($row['reported_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
