# Walkthrough - Corner Flag Arena Booking Application

We have successfully built the complete football field booking web application **Corner Flag Arena**. The application is structured, styled with a premium green-and-white jersey theme, and ready to be run locally.

## Project Structure

All files have been generated in the project folder: [C:\Users\haser\.gemini\antigravity\scratch\corner-flag-arena](file:///C:/Users/haser/.gemini/antigravity/scratch/corner-flag-arena)

```text
corner-flag-arena/
├── assets/
│   └── logo.png              # Cropped circular badge from the jersey image
├── config/
│   └── db.php                # PDO database connection
├── css/
│   └── style.css             # Elegant jersey-themed, mobile-responsive CSS
├── scripts/
│   └── crop_logo.php         # Utility PHP script used to extract the logo
├── uploads/                  # User payment screenshots upload directory
├── index.php                 # Landing page
├── register.php              # User registration (Name, Phone, Password)
├── login.php                 # User login page
├── logout.php                # Session destroyer
├── dashboard.php             # User dashboard (booking form, history, slot checkers)
├── profile.php               # User profile settings (name, phone, password change)
├── payment_instructions.php  # Opay account copy details and screenshot upload form
├── booking_success.php       # Post-booking checkmark confirmation success card
├── check_slots.php           # AJAX slot availability endpoint
├── book.php                  # Booking form processing & image verification
├── admin_login.php           # Admin login form (admin / admin123)
├── admin_dashboard.php       # Administrative panel (bookings management, users list)
├── admin_action.php          # Booking approve/reject actions
└── schema.sql                # Database schema file
```

---

## Database Setup

1. Open your MySQL client (e.g., **phpMyAdmin** at `http://localhost/phpmyadmin` in XAMPP).
2. Create a new database named `corner_flag_arena`.
3. Import the [schema.sql](file:///C:/Users/haser/.gemini/antigravity/scratch/corner-flag-arena/schema.sql) file. This will create:
   - `users` table: Stores registered player names, phone numbers, and encrypted passwords.
   - `bookings` table: Stores bookings tied to users, including date, time slots (4PM-5PM, 5PM-6PM, 6PM-7PM, 7PM-8PM), payment proof path, and approval status (`pending`, `approved`, `rejected`), with a unique constraint on (`booking_date`, `time_slot`) to prevent database-level double-bookings.

---

## How to Run the Web Application

1. **Move files to XAMPP**:
   Copy the contents of `C:\Users\haser\.gemini\antigravity\scratch\corner-flag-arena` into your local XAMPP web root directory, typically at:
   `C:\xampp\htdocs\corner-flag-arena`

2. **Start Servers**:
   Open the XAMPP Control Panel and start **Apache** and **MySQL**.

3. **Access the Site**:
   - For Users: Open your browser and navigate to `http://localhost/corner-flag-arena/`
   - For Admins: Open `http://localhost/corner-flag-arena/admin_login.php` or click the **Admin Portal** link in the footer of the page.

---

## How to Build the Android WebView App

1. **Open Android Studio**:
   Select **File > Open** (or **Import Project**) and select the directory:
   `C:\xampp\htdocs\corner-flag-arena\android-app`

2. **Sync and Compile**:
   - Wait for Android Studio to sync the Gradle build files.
   - Run the application on an emulator or a connected physical device by clicking the **Run** button.

3. **WebView Loopback configuration**:
   - In [MainActivity.java](file:///C:/Users/haser/.gemini/antigravity/scratch/corner-flag-arena/android-app/app/src/main/java/com/cornerflagarena/MainActivity.java), the app loads `http://10.0.2.2/corner-flag-arena/` which resolves to the local XAMPP Apache host from the Android Emulator loopback.
   - If hosting the web app online later, update the `APP_URL` string constant in `MainActivity.java`.

---

## Verification & Key Highlights

- **Android Mobile App Integration**:
  - **Splash Screen**: Displayed automatically with the cropped `logo.png` and App Title for 2.5 seconds using a custom fade-out alpha transition before unveiling the WebView.
  - **Offline Detection**: Uses the Android `ConnectivityManager` to monitor connections. If internet is lost, the WebView is hidden and a custom native retry layout is shown. Clicking "Retry" checks connectivity again and reloads the URL.
  - **Back Button Navigation**: Overrode the native back button. If the user is browsing pages inside the WebView, pressing back navigates back in WebView history. If they are on the homepage, pressing back exits the application.
  - **JavaScript and DOM storage**: Full configuration enabled to support dynamic AJAX slot checks and modals in WebView.
  - **Push Notification Channel**: Created a notification channel `booking_updates` in the app, preparing the APK structure to handle future FCM push notifications when you link it with an online database.
- **Logo Extraction**: We successfully wrote and executed [crop_logo.php](file:///C:/Users/haser/.gemini/antigravity/scratch/corner-flag-arena/scripts/crop_logo.php) which loaded your attached jersey image, cropped the circular badge on the chest, and saved it as [logo.png](file:///C:/Users/haser/.gemini/antigravity/scratch/corner-flag-arena/assets/logo.png). This logo is loaded in the header of all pages!
- **Double Booking Prevention**:
  - **Client-Side (AJAX)**: When a user selects a date on the dashboard, an AJAX request query is dispatched to `check_slots.php` to fetch all booked/pending slots for that day. The UI instantly disables and strikes through already booked slots.
  - **Database-Side**: Enforced via a MySQL `UNIQUE KEY (booking_date, time_slot)` constraint.
- **Payment Verification**: Users must upload an image as payment proof. The backend verifies the file mime-type and extension (restricting uploads to JPG, JPEG, PNG, GIF) and limits file size to 5MB before saving it with a unique name to the `uploads/` directory.
- **Admin Action Panel**: The admin panel features sidebar navigation, statistics summaries, booking logs, user registers, and an overlay modal to zoom in and inspect payment proof screenshots.
