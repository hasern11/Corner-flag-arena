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
$slot_prices_map = get_all_slot_prices();
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch user's booking stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total, 
                              SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                              SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved 
                       FROM bookings WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$total_bookings = $stats['total'] ?? 0;
$pending_bookings = $stats['pending'] ?? 0;
$approved_bookings = $stats['approved'] ?? 0;

// Fetch latest approved/rejected bookings to show as notifications
$notif_stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? AND status IN ('approved', 'rejected') ORDER BY created_at DESC LIMIT 3");
$notif_stmt->execute([$user_id]);
$recent_status_updates = $notif_stmt->fetchAll();

// Fetch user's bookings history
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC, time_slot DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

$error = '';
$success = '';

if (isset($_SESSION['booking_error'])) {
    $error = $_SESSION['booking_error'];
    unset($_SESSION['booking_error']);
}
if (isset($_SESSION['booking_success'])) {
    $success = $_SESSION['booking_success'];
    unset($_SESSION['booking_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Corner Flag Arena</title>
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <!-- Welcome Banner -->
        <div style="background: linear-gradient(135deg, var(--primary-dark) 0%, #0b130f 100%); color: var(--white); padding: 1.75rem 2.25rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid var(--primary); box-shadow: var(--shadow-md);">
            <div>
                <h2 style="font-size: 1.9rem; font-weight: 800; letter-spacing: -0.5px;">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
                <p style="opacity: 0.85; font-size: 0.95rem; margin-top: 2px;">Manage your reservations and book new game slots below.</p>
            </div>
            <div style="font-size: 2.5rem; animation: pulse 2s infinite; display: inline-block;">⚽</div>
        </div>

        <!-- Notification Banner System -->
        <?php if (!empty($recent_status_updates)): ?>
            <?php foreach ($recent_status_updates as $update): 
                $date_formatted = date('M d, Y', strtotime($update['booking_date']));
                $status_class = $update['status'] === 'approved' ? 'success' : 'danger';
                $status_icon = $update['status'] === 'approved' ? '✅' : '❌';
                $status_text = $update['status'] === 'approved' ? 'approved' : 'rejected';
            ?>
                <div class="notification-banner">
                    <span class="notification-icon"><?php echo $status_icon; ?></span>
                    <div class="notification-content">
                        <div class="notification-title" style="color: <?php echo $update['status'] === 'approved' ? 'var(--primary-dark)' : 'var(--accent-red)'; ?>">
                            Booking Update
                        </div>
                        <div class="notification-desc">
                            Your booking for <strong><?php echo htmlspecialchars($date_formatted); ?></strong> at <strong><?php echo htmlspecialchars($update['time_slot']); ?></strong> has been <strong><?php echo $status_text; ?></strong> by the administrator.
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <span>✅</span> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- KPI Cards -->
        <div class="dashboard-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <h3>Total Bookings</h3>
                    <div class="kpi-value"><?php echo $total_bookings; ?></div>
                </div>
                <div class="kpi-icon" style="color: var(--primary);">📋</div>
            </div>
            <div class="kpi-card pending">
                <div class="kpi-info">
                    <h3>Pending Approval</h3>
                    <div class="kpi-value"><?php echo $pending_bookings; ?></div>
                </div>
                <div class="kpi-icon" style="color: var(--accent-yellow);">⏳</div>
            </div>
            <div class="kpi-card approved">
                <div class="kpi-info">
                    <h3>Approved Bookings</h3>
                    <div class="kpi-value"><?php echo $approved_bookings; ?></div>
                </div>
                <div class="kpi-icon" style="color: var(--primary);">✅</div>
            </div>
        </div>

        <!-- Dashboard Layout -->
        <div class="dashboard-layout">
            <!-- Left Column: Booking Form -->
            <div class="panel">
                <h3 class="panel-title">Book a Field</h3>
                <form action="payment_instructions.php" method="GET" id="bookingForm">
                    <div class="form-group">
                        <label for="booking_date">Select Date</label>
                        <input type="date" id="booking_date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label style="margin-bottom: 0.75rem; display: block;">Select Time Slot</label>
                        
                        <!-- Fully Booked Banner -->
                        <div class="fully-booked-banner" id="fullyBookedBanner" style="display: none;">
                            🚫 Fully Booked for this Date
                        </div>

                        <div class="slots-container" id="slotsGrid">
                            <label class="slot-option" data-slot="4PM-5PM">
                                <input type="radio" name="slot" value="4PM-5PM" class="slot-radio" required>
                                4:00 PM - 5:00 PM
                            </label>
                            <label class="slot-option" data-slot="5PM-6PM">
                                <input type="radio" name="slot" value="5PM-6PM" class="slot-radio" required>
                                5:00 PM - 6:00 PM
                            </label>
                            <label class="slot-option" data-slot="6PM-7PM">
                                <input type="radio" name="slot" value="6PM-7PM" class="slot-radio" required>
                                6:00 PM - 7:00 PM
                            </label>
                            <label class="slot-option" data-slot="7PM-8PM">
                                <input type="radio" name="slot" value="7PM-8PM" class="slot-radio" required>
                                7:00 PM - 8:00 PM
                            </label>
                            <label class="slot-option" data-slot="8PM-9PM">
                                <input type="radio" name="slot" value="8PM-9PM" class="slot-radio" required>
                                8:00 PM - 9:00 PM
                            </label>
                            <label class="slot-option" data-slot="9PM-10PM">
                                <input type="radio" name="slot" value="9PM-10PM" class="slot-radio" required>
                                9:00 PM - 10:00 PM
                            </label>
                            <label class="slot-option" data-slot="10PM-11PM">
                                <input type="radio" name="slot" value="10PM-11PM" class="slot-radio" required>
                                10:00 PM - 11:00 PM
                            </label>
                        </div>
                        <p id="slots-info-text" style="font-size: 0.85rem; color: var(--text-muted); text-align: center;">Please select a date to check available slots.</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;" id="submitBtn" disabled>Proceed to Payment</button>

                    <!-- Live price display -->
                    <div id="priceBadge" style="display:none; margin-top: 1rem; padding: 0.85rem 1rem; background: rgba(25,195,125,0.08); border-left: 4px solid var(--primary); border-radius: var(--radius-sm); text-align: center;">
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Price for selected slot:</span>
                        <div id="priceDisplay" style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-top: 2px;"></div>
                    </div>
                </form>
            </div>

            <!-- Right Column: Booking History -->
            <div class="panel">
                <h3 class="panel-title">Booking History</h3>
                <?php if (empty($bookings)): ?>
                    <div style="text-align: center; padding: 4rem 1rem; color: var(--text-muted);">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.4;">🏟️</div>
                        <p>No reservations found. Book a field to get started!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time Slot</th>
                                    <th>Proof</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td style="font-weight: 700;"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-secondary zoomable-img" data-src="<?php echo htmlspecialchars($booking['payment_proof']); ?>" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; white-space: nowrap;">🖼️ View Receipt</button>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $booking['status']; ?>">
                                                <?php echo htmlspecialchars($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal for displaying zoomed image -->
    <div id="imageModal" class="modal">
        <span class="modal-close" id="modalClose">&times;</span>
        <img class="modal-content" id="modalImg">
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Corner Flag Arena. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Slot prices from PHP
            const slotPrices = <?php echo json_encode($slot_prices_map); ?>;

            const dateInput = document.getElementById('booking_date');
            const slotOptions = document.querySelectorAll('.slot-option');
            const slotsInfoText = document.getElementById('slots-info-text');
            const submitBtn = document.getElementById('submitBtn');
            const fullyBookedBanner = document.getElementById('fullyBookedBanner');

            // Handle date selection
            dateInput.addEventListener('change', function() {
                const selectedDate = this.value;
                if (!selectedDate) {
                    resetSlots();
                    return;
                }

                slotsInfoText.textContent = "Checking slot availability...";
                submitBtn.disabled = true;
                fullyBookedBanner.style.display = 'none';

                // Make AJAX call to fetch booked slots
                fetch('check_slots.php?date=' + selectedDate)
                    .then(response => response.json())
                    .then(bookedSlots => {
                        resetSlots();
                        
                        let availableCount = 0;
                        slotOptions.forEach(option => {
                            const radio = option.querySelector('.slot-radio');
                            const slotName = option.dataset.slot;

                            if (bookedSlots.includes(slotName)) {
                                option.classList.add('booked');
                                option.classList.remove('available');
                                radio.disabled = true;
                            } else {
                                option.classList.add('available');
                                option.classList.remove('booked');
                                radio.disabled = false;
                                availableCount++;
                            }
                        });

                        if (availableCount > 0) {
                            slotsInfoText.textContent = `${availableCount} time slot(s) available. Choose a green slot!`;
                            fullyBookedBanner.style.display = 'none';
                        } else {
                            slotsInfoText.textContent = "All slots are fully booked for this date.";
                            fullyBookedBanner.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        slotsInfoText.textContent = "Failed to load slot availability. Please try again.";
                    });
            });

            function resetSlots() {
                slotOptions.forEach(option => {
                    option.classList.remove('selected', 'booked', 'available');
                    const radio = option.querySelector('.slot-radio');
                    radio.checked = false;
                    radio.disabled = true;
                });
                submitBtn.disabled = true;
            }

            // Handle slot selection (visual state)
            slotOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('.slot-radio');
                    if (radio.disabled) return;

                    // Unselect others
                    slotOptions.forEach(o => o.classList.remove('selected'));
                    
                    // Select this
                    this.classList.add('selected');
                    radio.checked = true;

                    // Show price for this slot
                    const price = slotPrices[this.dataset.slot];
                    const priceBadge = document.getElementById('priceBadge');
                    const priceDisplay = document.getElementById('priceDisplay');
                    if (price) {
                        priceDisplay.textContent = '\u20a6' + price.toLocaleString();
                        priceBadge.style.display = 'block';
                    }

                    // Enable submit button
                    submitBtn.disabled = false;
                });
            });

            // Modal viewer for screenshots
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImg');
            const modalClose = document.getElementById('modalClose');
            const zoomableImgs = document.querySelectorAll('.zoomable-img');

            zoomableImgs.forEach(el => {
                el.addEventListener('click', function() {
                    modal.style.display = "block";
                    modalImg.src = this.dataset.src || this.src;
                });
            });

            modalClose.addEventListener('click', function() {
                modal.style.display = "none";
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>
