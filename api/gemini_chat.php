<?php
/**
 * KisanMitra AP — Google Gemini AI Agricultural Advisor REST Endpoint
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode((string)$raw, true);
$question = trim((string)($payload['message'] ?? ''));
$lang = (string)($payload['lang'] ?? 'en');
$district = (string)($payload['district'] ?? 'Guntur');

if ($question === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a question for KisanMitra AI.']);
    exit;
}

$systemInstruction = "You are KisanMitra AI, an expert Agricultural Extension Officer for the Government of Andhra Pradesh and ANGRAU (Acharya N.G. Ranga Agricultural University). "
    . "Answer concisely and practically in " . ($lang === 'te' ? "Telugu (తెలుగు) with clear agro-chemical names" : "English") . ". "
    . "Current Farmer District: {$district}, Andhra Pradesh. "
    . "Reference verified AP schemes when relevant: Annadata Sukhibhava + PM-KISAN provides ₹20,000/year (₹14,000 AP State + ₹6,000 Central in 3 installments); "
    . "APMIP Drip Irrigation gives 100% subsidy for SC/ST (<=5 acres, up to ₹2.18L) and 90% for other small/marginal farmers (<=5 acres, up to ₹2.18L); "
    . "e-Crop registration at the village Rythu Seva Kendram (RBK) is mandatory for PMFBY crop insurance (2% Kharif, 1.5% Rabi, 5% commercial crops).";

// Call Live Google Gemini API if API Key is configured
if (GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE' && function_exists('curl_init')) {
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . urlencode(GEMINI_API_KEY);
    $body = [
        'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => $question]]]
        ],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 450,
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_TIMEOUT        => 12,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $httpCode === 200) {
        $decoded = json_decode((string)$response, true);
        $replyText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($replyText) {
            echo json_encode(['status' => 'ok', 'model' => GEMINI_MODEL, 'reply' => $replyText]);
            exit;
        }
    }
}

// Built-in ANGRAU Knowledge Base Fallback (when running locally before setting GEMINI_API_KEY)
$qLower = mb_strtolower($question);
if (str_contains($qLower, 'chilli') || str_contains($qLower, 'curl') || str_contains($qLower, 'మిర్చి')) {
    $reply = "For Guntur Chilli upward leaf curl (Thrips) & downward curl (Mites): Install 12 blue sticky traps/acre. Spray Spinosad 45 SC @ 0.3 ml/L or Diafenthiuron 50 WP @ 1.25 g/L. Today's Guntur APMC Chilli modal rate is ₹24,920/Qtl (Max ₹28,500/Qtl).";
} elseif (str_contains($qLower, 'black gram') || str_contains($qLower, 'droop') || str_contains($qLower, 'urad') || str_contains($qLower, 'మినుము')) {
    $reply = "For Black Gram leaf drooping / dry root rot (Macrophomina): Drench root zone with Carbendazim 12% + Mancozeb 63% WP @ 2 g/litre of water. Treat future seeds with Bacillus subtilis @ 10 g/kg seed and spray 2% DAP at flowering.";
} elseif (str_contains($qLower, 'annadata') || str_contains($qLower, '20000') || str_contains($qLower, 'pm kisan') || str_contains($qLower, 'అన్నదాత')) {
    $reply = "Under Annadata Sukhibhava – PM KISAN, eligible Andhra Pradesh farmer families receive ₹20,000/year (₹14,000 AP Govt + ₹6,000 Central PM-KISAN) in 3 installments via Aadhaar-linked DBT. Check status at annadathasukhibhava.ap.gov.in with your Aadhaar number.";
} elseif (str_contains($qLower, 'drip') || str_contains($qLower, 'apmip') || str_contains($qLower, 'subsidy') || str_contains($qLower, 'డ్రిప్')) {
    $reply = "Under APMIP (G.O.Rt.No.82), SC/ST small & marginal farmers (≤5 acres) get 100% drip irrigation subsidy (up to ₹2,18,000), and other farmers (≤5 acres) get 90% subsidy (pay only 10%). Medium farmers (5–10 acres) get 90% in Rayalaseema/Prakasam (up to ₹3.46L) and 70% in Coastal AP (up to ₹3.10L).";
} else {
    $reply = "KisanMitra AI ({$district} RBK Desk): Ensure your plot is booked in e-Crop at your village Rythu Seva Kendram. For crop-specific NPK dosages, select your crop in the Advisory Desk above, or set your GEMINI_API_KEY in config/config.php for live generative responses.";
}

echo json_encode(['status' => 'ok', 'model' => GEMINI_MODEL . ' (Knowledge Base)', 'reply' => $reply]);
