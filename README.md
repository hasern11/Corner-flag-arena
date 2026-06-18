# 🏟️ Corner Flag Arena

Corner Flag Arena is a PHP + MySQL football field booking web application with an Android companion WebView app. This repository contains the complete codebase, configurations, and deployment strategies.

---

## 📋 Features

*   **Public Landing Page**: Features matching CSS aesthetics, location layout, and Kano Google Map integration.
*   **Booking System**: Interactive date-picker with client-side AJAX availability checks mapping slots up to 11 PM.
*   **Secure Payment Workflow**: Proof of payment image upload supporting secure validation triggers (`getimagesize()`) and execution sandboxing (.htaccess).
*   **Admin Dashboard**: Features sidebar navigation containing Reservation management, registered users lists, visual calendar grid views, and administrative settings.
*   **Production Readiness & Security**:
    *   Dynamic environment configurations.
    *   Hashed database administrative accounts.
    *   IP-bound database-backed rate limiting.
    *   Secure cookie flags (`HttpOnly`, `SameSite=Strict`, `session.use_strict_mode`).

---

## 💻 Tech Stack

*   **Backend**: PHP 8.2+
*   **Database**: MySQL
*   **Frontend**: Vanilla HTML5, Vanilla JavaScript, CSS variables (`style.css` Outfit font)
*   **Companion App**: Java + XML Android WebView client

---

## ⚙️ Local Development Setup

1. Clone this repository into your local environment:
   ```bash
   git clone https://github.com/hasern11/Corner-flag-arena.git
   ```
2. Move the project folder into your XAMPP server path (`htdocs/`).
3. Import the database layout located in `schema.sql` to your local MySQL instance.
4. Access the web layout at: `http://localhost/corner-flag-arena/`
5. Access the admin login panel at: `http://localhost/corner-flag-arena/admin_login.php` (seeded defaults: `admin` / `admin123`).
