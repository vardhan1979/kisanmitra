<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/services/weather_api.php';

$pdo = get_db();
$selectedDistrict = $_GET['district'] ?? 'Guntur';
$weather = get_district_weather(16.3067, 80.4365, $selectedDistrict);

// Handle Pest Ticket Form Submission via PDO + CSRF Check
$ticketMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_ticket') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $ticketMsg = 'Security token mismatch. Please refresh and try again.';
    } else {
        $code = 'KM-AP-2026-' . random_int(200, 999);
        $title = trim((string)($_POST['symptom_title'] ?? ''));
        $details = trim((string)($_POST['symptom_details'] ?? ''));
        $districtId = (int)($_POST['district_id'] ?? 1);
        $cropId = (int)($_POST['crop_id'] ?? 1);
        $severity = in_array($_POST['severity'] ?? '', ['Low', 'Moderate', 'High'], true) ? $_POST['severity'] : 'Moderate';

        $photoPath = 'assets/images/ap_major_crops.jpg';
        if (!empty($_FILES['leaf_photo']['tmp_name']) && $_FILES['leaf_photo']['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['leaf_photo']['tmp_name']);
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) && $_FILES['leaf_photo']['size'] <= 3 * 1024 * 1024) {
                $ext = pathinfo($_FILES['leaf_photo']['name'], PATHINFO_EXTENSION);
                $dest = 'uploads/ticket_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (@move_uploaded_file($_FILES['leaf_photo']['tmp_name'], __DIR__ . '/' . $dest)) {
                    $photoPath = $dest;
                }
            }
        }

        if ($pdo && $title !== '') {
            $stmt = $pdo->prepare('INSERT INTO pest_tickets (ticket_code, user_id, district_id, crop_id, symptom_title, symptom_details, photo_path, severity) VALUES (?, 1, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$code, $districtId, $cropId, $title, $details, $photoPath, $severity]);
        }
        $ticketMsg = "Ticket {$code} registered successfully and routed to your Mandal Rythu Seva Kendram (RBK) Agricultural Officer.";
    }
}
?>

<main class="flex-grow max-w-7xl mx-auto px-4 py-8 space-y-12">
    <!-- Hero Banner -->
    <section class="rounded-2xl overflow-hidden shadow-lg bg-[#14532D] text-white grid grid-cols-1 lg:grid-cols-12">
        <div class="p-8 lg:col-span-7 flex flex-col justify-center space-y-4">
            <span class="inline-block w-fit px-3 py-1 rounded-full bg-[#D97706] text-xs font-bold uppercase tracking-wider"><?= t('hero_badge') ?></span>
            <h1 class="text-3xl lg:text-4xl font-extrabold leading-tight"><?= t('hero_title') ?></h1>
            <p class="text-emerald-100 text-base leading-relaxed"><?= t('hero_desc') ?></p>
            <div class="flex flex-wrap gap-3 pt-2">
                <a href="#advisory" class="px-5 py-2.5 rounded-xl bg-[#16A34A] hover:bg-emerald-500 font-bold text-white shadow"><?= t('btn_get_advisory') ?></a>
                <a href="#schemes" class="px-5 py-2.5 rounded-xl bg-white text-[#14532D] hover:bg-slate-100 font-bold shadow"><?= t('btn_check_schemes') ?></a>
            </div>
        </div>
        <div class="lg:col-span-5">
            <img src="assets/images/hero_ap_farmers.jpg" alt="Andhra Pradesh Farmers at Rythu Seva Kendram" class="w-full h-full object-cover">
        </div>
    </section>

    <!-- Weather & Risk Section -->
    <section id="weather" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
            <h2 class="text-xl font-extrabold text-[#14532D]"><?= t('section_weather') ?></h2>
            <form method="get" class="flex items-center gap-2">
                <select name="district" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm font-medium">
                    <?php foreach (['Guntur', 'Krishna', 'Kurnool', 'East Godavari', 'Anantapuramu', 'SPSR Nellore'] as $d): ?>
                        <option value="<?= e($d) ?>" <?= $selectedDistrict === $d ? 'selected' : '' ?>><?= e($d) ?> District</option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border">
                <div class="text-xs text-slate-500">Temperature</div>
                <div class="text-2xl font-extrabold text-slate-800"><?= e((string)$weather['temp']) ?> °C</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border">
                <div class="text-xs text-slate-500">Humidity & Rain Prob.</div>
                <div class="text-2xl font-extrabold text-slate-800"><?= e((string)$weather['humidity']) ?>% / <?= e((string)$weather['rain_prob']) ?>%</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border">
                <div class="text-xs text-slate-500">Wind Speed</div>
                <div class="text-2xl font-extrabold text-slate-800"><?= e((string)$weather['wind_kmh']) ?> km/h</div>
            </div>
            <div class="p-4 rounded-xl border <?= e($weather['badge_class']) ?>">
                <div class="text-xs font-bold uppercase">Spraying Risk Level</div>
                <div class="text-xl font-extrabold"><?= e($weather['risk_level']) ?></div>
                <p class="text-xs mt-1"><?= e($weather['advice']) ?></p>
            </div>
        </div>
    </section>

    <!-- Pest Ticket Submission -->
    <section id="tickets" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <h2 class="text-xl font-extrabold text-[#14532D] mb-4"><?= t('section_tickets') ?></h2>
        <?php if ($ticketMsg !== ''): ?>
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold"><?= e($ticketMsg) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="submit_ticket">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Symptom Headline</label>
                <input type="text" name="symptom_title" required placeholder="e.g., Upward leaf curling in 45-day Guntur Chilli" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Upload Crop / Leaf Photo (JPG/PNG ≤ 3MB)</label>
                <input type="file" name="leaf_photo" accept="image/*" class="w-full border rounded-lg px-3 py-1.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Detailed Field Observation</label>
                <textarea name="symptom_details" rows="2" required class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Describe crop age, acreage, soil type, and recent sprays..."></textarea>
            </div>
            <div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#16A34A] text-white font-bold text-sm hover:bg-[#14532D]">Submit Ticket to RBK Officer</button>
            </div>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
