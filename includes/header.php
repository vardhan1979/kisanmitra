<?php
require_once __DIR__ . '/../config/config.php';
$currLang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= e($currLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('site_title') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Telugu:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', 'Noto Sans Telugu', sans-serif; background-color: #F8FAFC; color: #0F172A; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Top Government Ribbon -->
    <div class="bg-[#14532D] text-white text-xs py-2 px-4 border-b border-emerald-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-[#D97706] text-white font-semibold">GOVT OF AP</span>
                <span><?= t('dept_subtitle') ?></span>
            </div>
            <div class="flex items-center gap-4">
                <span>Helpline (RBK Toll-Free): <strong>155251</strong></span>
                <div class="flex items-center bg-emerald-950 rounded-lg p-0.5 border border-emerald-700">
                    <a href="?lang=en" class="px-2.5 py-1 rounded-md font-semibold <?= $currLang === 'en' ? 'bg-[#16A34A] text-white' : 'text-emerald-200' ?>">English</a>
                    <a href="?lang=te" class="px-2.5 py-1 rounded-md font-semibold <?= $currLang === 'te' ? 'bg-[#16A34A] text-white' : 'text-emerald-200' ?>">తెలుగు</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-40 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap justify-between items-center gap-4">
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#14532D] text-white flex items-center justify-center font-bold text-lg shadow">KM</div>
                <div>
                    <div class="font-extrabold text-lg text-[#14532D] leading-tight">KisanMitra <span class="text-[#D97706]">AP</span></div>
                    <div class="text-xs text-slate-500">Rythu Seva Kendram Digital Portal</div>
                </div>
            </a>
            <nav class="flex flex-wrap items-center gap-5 text-sm font-semibold text-slate-700">
                <a href="index.php#advisory" class="hover:text-[#16A34A]"><?= t('nav_advisory') ?></a>
                <a href="index.php#weather" class="hover:text-[#16A34A]"><?= t('nav_weather') ?></a>
                <a href="index.php#mandi" class="hover:text-[#16A34A]"><?= t('nav_mandi') ?></a>
                <a href="index.php#schemes" class="hover:text-[#16A34A]"><?= t('nav_schemes') ?></a>
                <a href="index.php#tickets" class="hover:text-[#16A34A]"><?= t('nav_tickets') ?></a>
                <a href="admin/dashboard.php" class="px-3.5 py-1.5 rounded-lg bg-[#14532D] text-white hover:bg-[#16A34A] transition"><?= t('nav_admin') ?></a>
            </nav>
        </div>
    </header>
