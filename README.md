# KisanMitra AP — Smart Crop Advisory & Farmer Support Portal (PHP 8 + MySQL)

A complete, production-ready final-semester project tailored for the **Department of Agriculture, Government of Andhra Pradesh (Rythu Seva Kendram / RBK Digital Companion)**.

## Quick Setup (XAMPP / MAMP / Laragon / LAMP)
1. Copy the `kisanmitra_ap` folder into `htdocs/` (XAMPP) or `/var/www/html/`.
2. Open **phpMyAdmin** or MySQL CLI and import `database.sql`:
   ```bash
   mysql -u root -p < database.sql
   ```
3. Open `config/config.php` and add your free API keys:
   - `GEMINI_API_KEY`: Get from https://aistudio.google.com/
   - `OPENWEATHER_API_KEY`: Get from https://openweathermap.org/api
   *(Note: Both services include automatic offline caching and ANGRAU fallback data so your demo works 100% reliably even without internet during your viva!)*
4. Visit `http://localhost/kisanmitra_ap/` in your browser.
