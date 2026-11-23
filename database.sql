-- ============================================================================
-- KisanMitra AP (Smart Crop Advisory & Farmer Support Portal for Andhra Pradesh)
-- Database Schema & Realistic AP Seed Data (MySQL 8.0+ / MariaDB 10.4+)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS kisanmitra_ap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kisanmitra_ap;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS weather_alerts;
DROP TABLE IF EXISTS pest_tickets;
DROP TABLE IF EXISTS schemes;
DROP TABLE IF EXISTS price_history;
DROP TABLE IF EXISTS mandi_prices;
DROP TABLE IF EXISTS advisories;
DROP TABLE IF EXISTS crops;
DROP TABLE IF EXISTS districts;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table (Farmers, RBK Agri Officers, State Admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(120) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('farmer', 'officer', 'admin') NOT NULL DEFAULT 'farmer',
    district_id INT DEFAULT NULL,
    mandal_name VARCHAR(100) DEFAULT NULL,
    preferred_lang ENUM('en', 'te') NOT NULL DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Andhra Pradesh Districts Table
CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_te VARCHAR(100) NOT NULL,
    agro_zone VARCHAR(120) NOT NULL,
    dominant_soils VARCHAR(200) NOT NULL,
    latitude DECIMAL(8,5) NOT NULL,
    longitude DECIMAL(8,5) NOT NULL,
    annual_rainfall_mm INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Crops Table
CREATE TABLE crops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name_en VARCHAR(100) NOT NULL,
    name_te VARCHAR(100) NOT NULL,
    scientific_name VARCHAR(120) NOT NULL,
    season ENUM('Kharif', 'Rabi', 'Both') NOT NULL DEFAULT 'Both',
    water_requirement ENUM('Low', 'Medium', 'High') NOT NULL,
    image_path VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Crop & Soil Advisories Table
CREATE TABLE advisories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    district_id INT DEFAULT NULL,
    season ENUM('Kharif', 'Rabi') NOT NULL,
    soil_type ENUM('Black Cotton', 'Red Soil', 'Sandy Loam', 'Alluvial') NOT NULL,
    sowing_window VARCHAR(120) NOT NULL,
    seed_rate_kg_per_acre DECIMAL(6,2) NOT NULL,
    spacing_cm VARCHAR(60) NOT NULL,
    npk_kg_per_acre VARCHAR(80) NOT NULL,
    fertilizer_schedule TEXT NOT NULL,
    irrigation_schedule TEXT NOT NULL,
    pest_management TEXT NOT NULL,
    source_reference VARCHAR(255) DEFAULT 'ANGRAU / NFSM Package of Practices',
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Daily Mandi Prices Table
CREATE TABLE mandi_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    district_id INT NOT NULL,
    mandi_name VARCHAR(120) NOT NULL,
    variety VARCHAR(100) NOT NULL,
    min_price DECIMAL(10,2) NOT NULL,
    max_price DECIMAL(10,2) NOT NULL,
    modal_price DECIMAL(10,2) NOT NULL,
    reported_date DATE NOT NULL,
    source_url VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Historical / Trend Price Series Table
CREATE TABLE price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    mandi_name VARCHAR(120) NOT NULL,
    period_label VARCHAR(60) NOT NULL,
    recorded_date DATE NOT NULL,
    modal_price DECIMAL(10,2) NOT NULL,
    series_type ENUM('recent_7d', 'angrau_historical') NOT NULL DEFAULT 'recent_7d',
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Government Schemes & Subsidies Table
CREATE TABLE schemes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    title_en VARCHAR(180) NOT NULL,
    title_te VARCHAR(180) NOT NULL,
    govt_level ENUM('AP State + Central', 'AP State Government', 'Central Government') NOT NULL,
    benefit_summary VARCHAR(200) NOT NULL,
    description_en TEXT NOT NULL,
    description_te TEXT NOT NULL,
    eligibility_rules TEXT NOT NULL,
    required_documents TEXT NOT NULL,
    official_portal_url VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Farmer Pest & Disease Tickets Table
CREATE TABLE pest_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    district_id INT NOT NULL,
    crop_id INT NOT NULL,
    symptom_title VARCHAR(180) NOT NULL,
    symptom_details TEXT NOT NULL,
    photo_path VARCHAR(255) DEFAULT NULL,
    severity ENUM('Low', 'Moderate', 'High') NOT NULL DEFAULT 'Moderate',
    status ENUM('Open', 'In Review', 'Resolved') NOT NULL DEFAULT 'Open',
    officer_reply TEXT DEFAULT NULL,
    replied_by VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. District Weather & Cyclone Alerts Table
CREATE TABLE weather_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district_id INT NOT NULL,
    risk_level ENUM('Low Risk', 'Moderate Risk', 'High Risk') NOT NULL,
    temp_celsius DECIMAL(4,1) NOT NULL,
    humidity_pct INT NOT NULL,
    wind_kmh DECIMAL(5,1) NOT NULL,
    rain_prob_pct INT NOT NULL,
    alert_headline_en VARCHAR(220) NOT NULL,
    alert_headline_te VARCHAR(220) NOT NULL,
    spraying_advice VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA: ANDHRA PRADESH DISTRICTS, CROPS, MANDIS, SCHEMES & TICKETS
-- ============================================================================

INSERT INTO districts (id, name_en, name_te, agro_zone, dominant_soils, latitude, longitude, annual_rainfall_mm) VALUES
(1, 'Guntur', 'గుంటూరు', 'Krishna Agro-Climatic Zone', 'Black Cotton (60%), Red Loam (30%), Alluvial (10%)', 16.30670, 80.43650, 889),
(2, 'Krishna (Vijayawada)', 'కృష్ణా (విజయవాడ)', 'Krishna Agro-Climatic Zone', 'Deltaic Alluvial (55%), Black Cotton (30%), Sandy Loam (15%)', 16.50620, 80.64800, 1028),
(3, 'Kurnool', 'కర్నూలు', 'Scarce Rainfall Zone (Rayalaseema)', 'Black Cotton (52%), Red Earth (38%), Mixed Loam (10%)', 15.82810, 78.03730, 670),
(4, 'East Godavari (Rajahmundry)', 'తూర్పు గోదావరి (రాజమండ్రి)', 'Godavari Agro-Climatic Zone', 'Alluvial Delta (65%), Red Loam (20%), Coastal Sandy (15%)', 17.00050, 81.80400, 1150),
(5, 'Anantapuramu', 'అనంతపురం', 'Scarce Rainfall Zone (Rayalaseema)', 'Red Soil (87.4%), Black Cotton (12.5%)', 14.68190, 77.60060, 553),
(6, 'SPSR Nellore', 'శ్రీ పొట్టి శ్రీరాములు నెల్లూరు', 'Southern Agro-Climatic Zone', 'Coastal Alluvial (45%), Red Soil (35%), Sandy Loam (20%)', 14.44260, 79.98650, 1080),
(7, 'Prakasam', 'ప్రకాశం', 'Krishna & Southern Transition Zone', 'Red Soil (51.3%), Black Soil (40.8%), Sandy Loam (5.9%)', 15.50570, 80.04990, 872),
(8, 'Visakhapatnam', 'విశాఖపట్నం', 'North Coastal Zone', 'Red Loamy (65%), Sandy Loam (25%), Lateritic (10%)', 17.68680, 83.21850, 1202);

-- Password for all demo accounts is: Kisan@2026 (bcrypt hash)
INSERT INTO users (id, full_name, phone, email, password_hash, role, district_id, mandal_name, preferred_lang) VALUES
(1, 'Y. N. Vardhan Reddy', '9848012345', 'vardhan.farmer@apagrisnet.in', '$2y$10$wK1fH9yJ5v8qZ0mX4nL2eO7rC6bA1dE8gH3jK5lM9nP0qR2sT4uV6', 'farmer', 1, 'Tenali', 'en'),
(2, 'Dr. K. Srinivasa Rao (RBK Officer)', '9440198765', 'rbk.guntur@ap.gov.in', '$2y$10$wK1fH9yJ5v8qZ0mX4nL2eO7rC6bA1dE8gH3jK5lM9nP0qR2sT4uV6', 'officer', 1, 'Guntur Rural', 'te'),
(3, 'AP Agriculture State Admin', '9000011111', 'admin@apagrisnet.gov.in', '$2y$10$wK1fH9yJ5v8qZ0mX4nL2eO7rC6bA1dE8gH3jK5lM9nP0qR2sT4uV6', 'admin', 2, 'Vijayawada', 'en');

INSERT INTO crops (id, slug, name_en, name_te, scientific_name, season, water_requirement, image_path) VALUES
(1, 'chilli', 'Dry Red Chilli (Guntur Mirchi)', 'ఎండు మిర్చి (గుంటూరు మిర్చి)', 'Capsicum annuum', 'Both', 'Medium', 'assets/images/ap_major_crops.jpg'),
(2, 'paddy', 'Paddy (Fine / Grade-A)', 'వరి (ధాన్యం)', 'Oryza sativa', 'Both', 'High', 'assets/images/hero_ap_farmers.jpg'),
(3, 'cotton', 'Cotton (Kapas / Bramha)', 'పత్తి (కపాస్)', 'Gossypium hirsutum', 'Kharif', 'Medium', 'assets/images/ap_major_crops.jpg'),
(4, 'groundnut', 'Groundnut (Kadiri / Pod)', 'వేరుశనగ', 'Arachis hypogaea', 'Both', 'Low', 'assets/images/ap_major_crops.jpg'),
(5, 'blackgram', 'Black Gram (Urad Whole / Minumulu)', 'మినుములు (మినుము)', 'Vigna mungo', 'Both', 'Low', 'assets/images/ap_major_crops.jpg'),
(6, 'maize', 'Maize (Hybrid Corn /okkajonna)', 'మొక్కజొన్న', 'Zea mays', 'Both', 'Medium', 'assets/images/ap_major_crops.jpg');

INSERT INTO advisories (crop_id, district_id, season, soil_type, sowing_window, seed_rate_kg_per_acre, spacing_cm, npk_kg_per_acre, fertilizer_schedule, irrigation_schedule, pest_management, source_reference) VALUES
(1, 1, 'Kharif', 'Black Cotton', 'July 15 – August 31 (Nursery); Transplanting at 35–40 DAS', 0.60, '60 x 45 cm (or 75 x 30 cm)', '120 : 24 : 48 kg NPK/acre', 'Basal: 24 kg P2O5 + 10 t FYM/ac. Top dressing: Split N (120 kg) & K2O (48 kg) into 4 equal doses at 30, 60, 90, and 120 DAT. Spray 0.2% Borneo/Micronutrient mix at flowering.', 'Irrigate every 7–10 days in Black Cotton soils depending on monsoon breaks. Avoid water stagnation for >24 hrs to prevent Collar Rot. Adopt APMIP drip fertigation to cut water & fertilizer use by 50%.', 'Leaf Curl (Thrips/Mites): Install 12 blue/yellow sticky traps/acre; spray Spinosad 45 SC @ 0.3 ml/L or Diafenthiuron @ 1.25 g/L. Fruit Borer: Spray Chlorantraniliprole 18.5 SC @ 0.3 ml/L.', 'ANGRAU Lam Guntur Chilli Package'),
(2, 2, 'Kharif', 'Alluvial', 'June 10 – July 25 (Godavari & Krishna Delta)', 20.00, '20 x 15 cm (Kharif) / 15 x 15 cm (Rabi)', '36 : 24 : 24 kg NPK/acre + 20 kg ZnSO4', 'Basal: 1/3 N + Full P (24 kg) + 1/2 K (12 kg) + 20 kg Zinc Sulphate/acre. 1st Top Dressing (Active Tillering, 25 DAT): 1/3 N. 2nd Top Dressing (Panicle Initiation, 45 DAT): 1/3 N + 1/2 K.', 'Maintain 2 cm thin water film from transplanting to tillering, then 5 cm submergence up to dough stage. Drain field 10 days before harvest.', 'Stem Borer & Gall Midge: Apply Cartap Hydrochloride 4G @ 8 kg/acre or spray Chlorantraniliprole @ 0.3 ml/L. Brown Plant Hopper (BPH): Form alleyways of 20 cm every 2 meters; spray Pymetrozine 50 WG @ 120 g/acre.', 'ANGRAU Maruteru Rice Research Station'),
(3, 3, 'Kharif', 'Black Cotton', 'June 15 – July 31 (with onset of 75 mm monsoon rain)', 1.00, '90 x 60 cm (or 120 x 60 cm for hybrids)', '48 : 24 : 24 kg NPK/acre', 'Basal: Full P (24 kg) at sowing. Split N (48 kg) & K (24 kg) at 30, 60, and 90 DAS by pocketing 7 cm away from plant base. Foliar spray of 2% KNO3 at boll development.', 'Rainfed in deep black soils with 2–3 protective life-saving irrigations during square formation and peak boll development (90–110 DAS).', 'Pink Bollworm: Install 8 pheromone traps/acre at 45 DAS; spray Profenofos @ 2 ml/L or Emamectin Benzoate @ 0.5 g/L when Rosette flowers exceed 10% ETL.', 'ANGRAU Nandyal Cotton Advisory'),
(4, 5, 'Kharif', 'Red Soil', 'June 15 – July 31 (Kharif) / Nov 15 – Dec 15 (Rabi)', 60.00, '30 x 10 cm (Kharif) / 22.5 x 10 cm (Rabi)', '8 : 16 : 20 kg NPK/acre + 200 kg Gypsum', 'Apply 8 kg N, 16 kg P2O5, and 20 kg K2O/acre entirely as basal dose at 4 cm depth. Broadcast 200 kg Gypsum/acre at peak flowering (40–45 DAS) close to plant base for pod filling.', 'Critical moisture stages: Flowering (35 DAS), Pegging (45–55 DAS), and Pod Development (65–85 DAS). Use sprinkler/drip irrigation in Anantapur red soils.', 'Seed treatment with Tebuconazole 2 DS @ 1 g/kg seed + Trichoderma viride @ 4 g/kg. For Leaf Miner & Tikka Leaf Spot: Spray Hexaconazole @ 2 ml/L.', 'ANGRAU Kadiri Groundnut Research Station & TNAU'),
(5, 6, 'Rabi', 'Sandy Loam', 'October 15 – November 30 (Upland & Rice-Fallow Rabi)', 8.00, '30 x 10 cm', '8 : 20 : 0 kg NPK/acre', 'Apply 8 kg N and 20 kg P2O5/acre (approx. 45 kg DAP/acre) as basal for upland crop. Treat seeds with Bacillus subtilis @ 10 g/kg + Rhizobium biofertilizer. Spray 2% DAP at flowering and 15 days later.', 'Grows primarily on residual soil moisture in Krishna/Nellore rice fallows; provide 1–2 light irrigations at flowering (30 DAS) and pod filling (50 DAS) in upland plots.', 'Leaf Drooping / Wilt / Root Rot: Drench or treat with Carbendazim + Mancozeb (Saaf) @ 2 g/L or Trichoderma @ 10 g/kg seed. Yellow Mosaic Virus (Whitefly): Spray Acetamiprid 20 SP @ 0.4 g/L.', 'ANGRAU Lam Pulses Package & TNAU Agritech'),
(6, 3, 'Rabi', 'Red Soil', 'October 15 – November 30 (Rabi) / June 15 – July 15 (Kharif)', 8.00, '60 x 20 cm', '80 : 24 : 20 kg NPK/acre + 20 kg ZnSO4', 'Basal: 1/3 N + Full P (24 kg) + 1/2 K + 20 kg ZnSO4/acre. Dibble seeds at 4 cm depth. Top dress 1/3 N at knee-high stage (30 DAS) and 1/3 N + 1/2 K at tasseling (50–55 DAS). Foliar 0.5% ZnSO4 if deficiency appears.', 'Irrigate at 4 critical stages: Seedling, Knee-high (30 DAS), Tasseling/Silking (55 DAS), and Grain filling (75 DAS). Avoid water stress during silking.', 'Fall Armyworm (FAW): Whorl application of Emamectin Benzoate 5 SG @ 0.4 g/L or Spinetoram 11.7 SC @ 0.5 ml/L at 15–25 DAS. Pre-emergence weed control: Atrazine @ 200 g/acre at 3–5 DAS.', 'NFSM Maize Ready Reckoner & ANGRAU');

INSERT INTO mandi_prices (crop_id, district_id, mandi_name, variety, min_price, max_price, modal_price, reported_date, source_url) VALUES
(1, 1, 'Guntur APMC Mirchi Yard', 'Dry Red Chilli (Teja / Sannam)', 8000.00, 28500.00, 24920.00, '2026-11-17', 'https://www.kisandeals.com/mandiprices/DRY-CHILLIES/ANDHRA-PRADESH/GUNTUR-APMC'),
(1, 7, 'Andhra Pradesh 8-Mandi Avg', 'Dry Red Chilli (Standard)', 14500.00, 27500.00, 23339.00, '2026-11-16', 'https://www.kisandeals.com/mandiprices/DRY-CHILLIES/ANDHRA-PRADESH/ALL'),
(3, 3, 'Adoni APMC (Kurnool)', 'Cotton (Bramha / Kapas)', 4800.00, 9539.00, 9129.00, '2026-11-13', 'https://www.commodityonline.com/mandiprices/cotton/andhra-pradesh/adoni'),
(3, 3, 'Mantralayam APMC (Kurnool)', 'Cotton (Bramha)', 6500.00, 9550.00, 9150.00, '2026-11-13', 'https://commodityonline.com/mandiprices/district/andhra-pradesh/kurnool/cotton'),
(3, 1, 'Phirangipuram / Guntur APMC', 'Cotton (Unginned)', 7200.00, 8400.00, 7700.00, '2026-11-15', 'https://www.commodityonline.com/mandiprices/district/andhra-pradesh/guntur/cotton'),
(4, 3, 'Adoni APMC (Kurnool)', 'Groundnut (Pod)', 4800.00, 9539.00, 9129.00, '2026-11-13', 'https://agriplus.in/prices/all/andhra-pradesh/kurnool'),
(5, 2, 'Andhra Pradesh 4-Mandi Avg', 'Black Gram (Urad Whole)', 8250.00, 9900.00, 8987.00, '2026-11-17', 'https://www.kisandeals.com/mandiprices/BLACK-GRAM-URD-BEANS-WHOLE/ANDHRA-PRADESH/ALL'),
(2, 4, 'Godavari / AP State Avg', 'Paddy (Fine / Superfine Grade-A)', 2369.00, 2600.00, 2487.29, '2026-11-08', 'https://www.commodityonline.com/mandiprices/paddy-finesuperfinegrade-a/andhra-pradesh'),
(6, 3, 'Kurnool APMC', 'Maize (Hybrid Yellow)', 2717.00, 2950.00, 2870.00, '2026-11-13', 'https://www.commodityonline.com/mandiprices/maize/andhra-pradesh/kurnool'),
(6, 2, 'Andhra Pradesh State Avg', 'Maize (Standard)', 1800.00, 2870.00, 2592.71, '2026-11-16', 'https://www.commodityonline.com/mandiprices/maize/andhra-pradesh');

INSERT INTO price_history (crop_id, mandi_name, period_label, recorded_date, modal_price, series_type) VALUES
(1, 'Guntur / AP Official Series', 'Sep 2021', '2021-09-30', 10457.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', 'Dec 2021', '2021-12-31', 13131.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', 'Nov 2022', '2022-11-30', 24313.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', '2023-24 Avg', '2024-05-31', 18610.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', 'Jul 2025', '2025-07-31', 11779.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', '2025-26 Avg', '2026-01-31', 15240.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', 'Mar 2026', '2026-03-31', 19321.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', '16 Nov 2026 (AP Avg)', '2026-11-16', 23339.00, 'angrau_historical'),
(1, 'Guntur / AP Official Series', '17 Nov 2026 (Guntur)', '2026-11-17', 24920.00, 'angrau_historical');

INSERT INTO schemes (code, title_en, title_te, govt_level, benefit_summary, description_en, description_te, eligibility_rules, required_documents, official_portal_url, image_path) VALUES
('ANNADATA_PMKISAN', 'Annadata Sukhibhava – PM KISAN Combined Support', 'అన్నదాత సుఖీభవ – పీఎం కిసాన్ పథకం', 'AP State + Central', '₹20,000 / Year (₹14,000 AP Govt + ₹6,000 Central PM-KISAN in 3 Installments)', 'Flagship direct income support scheme of the Government of Andhra Pradesh launched on 2 August 2025. Replaces the earlier YSR Rythu Bharosa and tops up Central PM-KISAN (₹6,000/yr) with ₹14,000/yr from the State budget so each eligible farmer family receives ₹20,000 annually via Aadhaar-seeded DBT (two installments of ₹7,000 and one of ₹6,000). On 20 June 2026, ₹3,125.47 crore was disbursed to 46,85,838 farmer families.', 'ఆంధ్రప్రదేశ్ ప్రభుత్వం 2 ఆగస్టు 2025న ప్రారంభించిన ప్రధాన రైతు ఆర్థిక భరోసా పథకం. కేంద్ర పీఎం కిసాన్ ₹6,000 లకు రాష్ట్ర ప్రభుత్వం ₹14,000 కలిపి ఏడాదికి మూడు విడతల్లో మొత్తం ₹20,000 నేరుగా రైతు ఆధార్ లింక్డ్ బ్యాంక్ ఖాతాకు (DBT) జమ చేస్తుంది.', 'Small, marginal, and landowning farmer families in AP (45.69 lakh landowning + 1.16 lakh RoFR families covered in June 2026 release); tenant farmers with valid CCRC cards via Rythu Seva Kendram.', 'Aadhaar Card, Webland / 1B Adangal Land Record, Active e-KYC on Portal, NPCI Aadhaar-Seeded Bank Account, CCRC Card (for Tenant Farmers)', 'https://annadathasukhibhava.ap.gov.in', 'assets/images/hero_ap_farmers.jpg'),
('APMIP_DRIP', 'AP Micro Irrigation Project (APMIP Drip & Sprinkler Subsidy)', 'ఆంధ్రప్రదేశ్ సూక్ష్మ నీటిపారుదల పథకం (APMIP డ్రిప్ సబ్సిడీ)', 'AP State + Central', 'Up to 100% Subsidy for SC/ST & 90% for Small/Marginal Farmers (up to ₹2.18L–₹4.00L)', 'Implemented under AP G.O.Rt.No.82 & Per Drop More Crop. Saves irrigation water up to 50%, reduces fertilizer cost by 50%, saves labor up to 70%, and increases crop yields by >30%. SC/ST small & marginal farmers (≤5 acres) get 100% subsidy up to ₹2,18,000; other farmers (≤5 acres) get 90% subsidy up to ₹2,18,000; Rayalaseema/Prakasam medium farmers (5–10 acres) get 90% up to ₹3,46,000; Coastal AP medium farmers (5–10 acres) get 70% up to ₹3,10,000; large farmers (>10 acres) get 50% up to ₹4,00,000. Sprinklers carry 50% subsidy up to ₹19,000.', 'డ్రిప్ మరియు స్ప్రింక్లర్ సాగునీటి పరికరాలపై చిన్న, సన్నకారు ఎస్సీ/ఎస్టీ రైతులకు (5 ఎకరాల లోపు) 100% సబ్సిడీ (గరిష్టంగా ₹2,18,000), ఇతర చిన్న/సన్నకారు రైతులకు 90% సబ్సిడీ, రాయలసీమ/ప్రకాశం మధ్యతరహా రైతులకు (5-10 ఎకరాలు) 90% సబ్సిడీ (గరిష్టంగా ₹3,46,000) అందిస్తుంది.', 'Landowning farmers in Andhra Pradesh with valid water source (borewell/farm pond) and biometric registration at the village Rythu Seva Kendram / Horticulture Officer.', 'Aadhaar Card, 1B / Adangal Copy, Soil & Water Test Report, Bank Passbook, Passport Photo', 'https://krishna.ap.gov.in/apmip/', 'assets/images/ap_schemes_drip.jpg'),
('ECROP_RBK', 'AP e-Crop Digital Booking & Rythu Seva Kendram (RBK) Services', 'ఈ-క్రాప్ (e-Crop) నమోదు & రైతు సేవా కేంద్రం సేవలు', 'AP State Government', '100% Free Geo-Tagged Crop Registration, Soil Testing & Certified Inputs at Village Level', 'Every sown plot in Andhra Pradesh is digitally geo-tagged and verified under e-Crop through village-level Rythu Seva Kendrams (RBKs). Mandatory prerequisite for claiming crop insurance, cyclone relief, MSP procurement at Rythu Bazaars/APMARKFED, and interest-free crop loans.', 'రాష్ట్రంలోని ప్రతి ఎకరా సాగు వివరాలను గ్రామ స్థాయి రైతు సేవా కేంద్రం (RBK) ద్వారా జియో-ట్యాగింగ్ తో ఈ-క్రాప్ లో నమోదు చేస్తారు. పంటల బీమా, పంట నష్టపరిహారం, మద్దతు ధరకు ధాన్యం విక్రయానికి ఇది తప్పనిసరి.', 'All cultivators (landowners and CCRC tenant farmers) cultivating Kharif or Rabi crops in Andhra Pradesh.', 'Aadhaar Number, Survey Number / Webland Passbook, Mobile Number for e-Kyc Biometric / OTP authentication at RBK', 'https://karshak.ap.gov.in', 'assets/images/hero_ap_farmers.jpg'),
('PMFBY_INSURANCE', 'Pradhan Mantri Fasal Bima Yojana (PMFBY Crop Insurance)', 'ప్రధానమంత్రి ఫసల్ బీమా యోజన (పంటల బీమా)', 'AP State + Central', 'Farmer Pays Only 1.5% (Rabi), 2% (Kharif), or 5% (Commercial Crops) Premium', 'Comprehensive yield-loss and cyclone/drought crop insurance covering pre-sowing to post-harvest losses. In a drought or cyclone year, insured farmers can receive up to 25% mid-season advance and full yield-loss compensation.', 'తుఫాను, వరదలు, కరువు వల్ల పంట నష్టపోయిన రైతులకు పూర్తి బీమా భద్రత. ఖరీఫ్ ఆహార పంటలకు 2%, రబీ పంటలకు 1.5%, వాణిజ్య/ఉద్యాన పంటలకు (మిర్చి, పత్తి) 5% మాత్రమే రైతు ప్రీమియం చెల్లించాలి.', 'All farmers growing notified crops in notified mandals who enroll before the seasonal cutoff (typically July 31 for Kharif, Dec 31 for Rabi) with e-Crop booking.', 'e-Crop Booking Receipt, Aadhaar Card, Land Passbook / CCRC, Bank Account Details, Premium Payment Receipt', 'https://pmfby.gov.in', 'assets/images/ap_major_crops.jpg');

INSERT INTO pest_tickets (ticket_code, user_id, district_id, crop_id, symptom_title, symptom_details, photo_path, severity, status, officer_reply, replied_by) VALUES
('KM-AP-2026-101', 1, 1, 1, 'Upward leaf curling and flower drop in 45-day Guntur Chilli crop', 'Leaves in our 2.5-acre Teja chilli plot near Tenali are curling upward (boat shape) and flowers are dropping after dry spell.', 'assets/images/ap_major_crops.jpg', 'High', 'Resolved', 'Diagnosed as Thrips (Scirtothrips dorsalis) infestation. Install 12 blue sticky traps/acre immediately and spray Spinosad 45 SC @ 0.3 ml/litre (60 ml in 200L water/acre). Avoid synthetic pyrethroids which trigger mite resurgence.', 'Dr. K. Srinivasa Rao (RBK Guntur)'),
('KM-AP-2026-102', 1, 6, 5, 'Sudden leaf drooping and basal stem blackening in Black Gram (30 DAS)', 'In our Rabi rice-fallow black gram plot, scattered patches show sudden leaf drooping and dark brown lesions near soil line.', 'assets/images/ap_major_crops.jpg', 'Moderate', 'Resolved', 'Typical Dry Root Rot / Wilt (Macrophomina phaseolina). Drench affected patches with Carbendazim 12% + Mancozeb 63% WP @ 2.0 g/litre of water around the root zone, and spray 2% DAP foliar nutrient to boost recovery.', 'Dr. K. Srinivasa Rao (RBK Guntur)');

INSERT INTO weather_alerts (district_id, risk_level, temp_celsius, humidity_pct, wind_kmh, rain_prob_pct, alert_headline_en, alert_headline_te, spraying_advice) VALUES
(1, 'Low Risk', 30.5, 62, 11.5, 15, 'Clear skies across Guntur & Krishna Delta; favorable for foliar spray and chilli inter-cultivation.', 'గుంటూరు & కృష్ణా డెల్టాలో పొడి వాతావరణం; పురుగుమందుల పిచికారీకి అనుకూలం.', 'SAFE TO SPRAY: Wind speed is below 15 km/h and rain probability is 15%.'),
(3, 'Moderate Risk', 33.2, 44, 19.8, 20, 'Gusty afternoon winds in Kurnool & Adoni belt; soil moisture depletion likely in rainfed plots.', 'కర్నూలు & ఆదోని ప్రాంతంలో మధ్యాహ్నం బలమైన గాలులు; బెట్ట పరిస్థితులు.', 'CAUTION: Avoid pesticide spraying between 11 AM and 4 PM due to 19.8 km/h wind drift; spray early morning.'),
(6, 'High Risk', 27.8, 86, 28.4, 78, 'Bay of Bengal coastal trough alert for SPSR Nellore; heavy rain spells expected in next 48 hours.', 'నెల్లూరు తీర ప్రాంతంలో బంగాళాఖాత ద్రోణి ప్రభావం; రాగల 48 గంటల్లో భారీ వర్ష సూచన.', 'DO NOT SPRAY: High rain wash-off risk (78%) and strong winds (28.4 km/h). Clear field drainage channels.');
