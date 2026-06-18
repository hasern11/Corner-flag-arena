<?php
require_once 'config/security.php';
session_start();
send_security_headers();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php';
require_once 'config/settings.php';

$date = $_GET['date'] ?? '';
$slot = $_GET['slot'] ?? '';

if (empty($date) || empty($slot)) {
    header("Location: dashboard.php");
    exit();
}

$booking_price = get_slot_price($slot);
$formatted_price = '&#8358;' . number_format($booking_price);

// Format date for display
$formatted_date = date('F d, Y', strtotime($date));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Instructions - Corner Flag Arena</title>
    <link rel="icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo-wrapper">
                <img src="assets/logo.png" alt="Corner Flag Arena Logo">
                <div class="logo-text">
                    CORNER FLAG
                    <span>ARENA</span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container" style="max-width: 650px;">
        <div class="panel">
            <h2 class="panel-title">Payment Instructions</h2>
            
            <div class="instruction-box">
                <p style="font-size: 1.05rem; margin-bottom: 1rem;">To secure your slot for <strong><?php echo htmlspecialchars($formatted_date); ?></strong> at <strong><?php echo htmlspecialchars($slot); ?></strong>, please transfer <strong style="color: var(--primary);"><?php echo $formatted_price; ?></strong> to the account details below:</p>
                
                <div class="bank-details-card">
                    <div style="margin-bottom: 0.75rem;">
                        <span style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 700;">Bank Name</span>
                        <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-dark);">Opay</div>
                    </div>
                    <div style="margin-bottom: 0.75rem;">
                        <span style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 700;">Account Name</span>
                        <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-dark);">Corner Flag Arena</div>
                    </div>
                    <div style="margin-bottom: 0.75rem;">
                        <span style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 700;">Account Number</span>
                        <div class="copy-container">
                            <span class="account-num" id="accountNum">8061399073</span>
                            <button type="button" class="btn-copy" id="btnCopy">Copy</button>
                        </div>
                    </div>
                    <div style="border-top: 2px dashed var(--primary); padding-top: 0.85rem; margin-top: 0.25rem;">
                        <span style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 700;">Amount to Transfer</span>
                        <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; margin-top: 2px;"><?php echo $formatted_price; ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Per slot (1 hour)</div>
                    </div>
                </div>

                <p style="font-size: 0.9rem; color: var(--text-muted);">Please take a screenshot of the payment receipt. You will need to upload it below to verify your booking.</p>
            </div>

            <!-- Upload payment proof form -->
            <form action="book.php" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="booking_date" value="<?php echo htmlspecialchars($date); ?>">
                <input type="hidden" name="time_slot" value="<?php echo htmlspecialchars($slot); ?>">

                <div class="form-group">
                    <label style="font-size: 1.05rem; margin-bottom: 0.75rem;">Upload Payment Receipt Screenshot</label>
                    <div class="file-upload-zone" id="fileUploadZone">
                        <div class="file-upload-icon">📷</div>
                        <div class="file-upload-text">Click or Drag & Drop screenshot here</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">PNG, JPG, JPEG, GIF (Max 5MB)</div>
                        <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required>
                    </div>
                    <img id="uploadPreview" class="file-upload-preview" alt="Payment Proof Preview">
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="dashboard.php" class="btn btn-secondary" style="flex: 1;">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="flex: 2;" id="submitBtn" disabled>Submit Booking Proof</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Corner Flag Arena. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Copy Account Number
            const btnCopy = document.getElementById('btnCopy');
            const accountNumText = document.getElementById('accountNum').textContent;

            btnCopy.addEventListener('click', function() {
                navigator.clipboard.writeText(accountNumText).then(() => {
                    btnCopy.textContent = 'Copied!';
                    btnCopy.style.backgroundColor = '#0f764c';
                    btnCopy.style.color = '#ffffff';
                    setTimeout(() => {
                        btnCopy.textContent = 'Copy';
                        btnCopy.style.backgroundColor = 'var(--primary)';
                        btnCopy.style.color = '#0b130f';
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            });

            // Handle file upload preview
            const fileInput = document.getElementById('payment_proof');
            const uploadPreview = document.getElementById('uploadPreview');
            const submitBtn = document.getElementById('submitBtn');

            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        uploadPreview.src = e.target.result;
                        uploadPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                    submitBtn.disabled = false;
                } else {
                    uploadPreview.style.display = 'none';
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
</body>
</html>
