<?php
require_once 'config/security.php';
session_start();
send_security_headers();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'config/db.php';
require_once 'config/settings.php';

// Fetch overall KPI stats
$stats_stmt = $pdo->query("SELECT COUNT(*) as total, 
                                 SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                                 SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved 
                          FROM bookings");
$stats = $stats_stmt->fetch();

$total_bookings = $stats['total'] ?? 0;
$pending_bookings = $stats['pending'] ?? 0;
$approved_bookings = $stats['approved'] ?? 0;
$booking_price = (int) get_setting('booking_price', '15000');
$total_revenue = $approved_bookings * $booking_price;

$view = $_GET['view'] ?? 'bookings';

// Fetch bookings or users depending on view
if ($view === 'users') {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} elseif ($view === 'calendar') {
    // Fetch all pending and approved bookings for the calendar visualization
    $stmt = $pdo->query("SELECT b.*, u.name as user_name, u.phone as user_phone 
                         FROM bookings b 
                         JOIN users u ON b.user_id = u.id 
                         WHERE b.status IN ('pending', 'approved') 
                         ORDER BY b.booking_date ASC, b.time_slot ASC");
    $calendar_bookings = $stmt->fetchAll();
} elseif ($view === 'settings') {
    $slot_prices = get_all_slot_prices();
} else {
    $stmt = $pdo->query("SELECT b.*, u.name as user_name, u.phone as user_phone 
                         FROM bookings b 
                         JOIN users u ON b.user_id = u.id 
                         ORDER BY b.booking_date DESC, b.time_slot DESC");
    $bookings = $stmt->fetchAll();
}

$error = '';
$success = '';

if (isset($_SESSION['admin_error'])) {
    $error = $_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}
if (isset($_SESSION['admin_success'])) {
    $success = $_SESSION['admin_success'];
    unset($_SESSION['admin_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Corner Flag Arena</title>
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
                <li><span style="background: var(--accent-yellow); color: var(--primary-dark); font-weight: 700; padding: 4px 10px; border-radius: var(--radius-sm); font-size: 0.85rem; text-transform: uppercase;">Admin Mode</span></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <ul class="admin-sidebar-nav">
                <li>
                    <a href="admin_dashboard.php?view=bookings" class="<?php echo $view === 'bookings' ? 'active' : ''; ?>">
                        📋 Bookings List
                    </a>
                </li>
                <li>
                    <a href="admin_dashboard.php?view=calendar" class="<?php echo $view === 'calendar' ? 'active' : ''; ?>">
                        📅 Visual Calendar
                    </a>
                </li>
                <li>
                    <a href="admin_dashboard.php?view=users" class="<?php echo $view === 'users' ? 'active' : ''; ?>">
                        👥 Registered Users
                    </a>
                </li>
                <li>
                    <a href="admin_dashboard.php?view=settings" class="<?php echo $view === 'settings' ? 'active' : ''; ?>">
                        ⚙️ Settings
                    </a>
                </li>
                <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                    <a href="index.php" style="opacity: 0.8;">
                        🏠 Go to Main Page
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content Panel -->
        <main class="admin-main">
            <!-- Admin Alert Messages -->
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

            <!-- KPI Summary Cards -->
            <div class="dashboard-grid" style="margin-bottom: 2rem;">
                <div class="kpi-card" style="border-left-color: var(--primary);">
                    <div class="kpi-info">
                        <h3>Total Reservations</h3>
                        <div class="kpi-value"><?php echo $total_bookings; ?></div>
                    </div>
                    <div class="kpi-icon">📋</div>
                </div>
                <div class="kpi-card pending">
                    <div class="kpi-info">
                        <h3>Awaiting Verification</h3>
                        <div class="kpi-value"><?php echo $pending_bookings; ?></div>
                    </div>
                    <div class="kpi-icon">⏳</div>
                </div>
                <div class="kpi-card approved">
                    <div class="kpi-info">
                        <h3>Approved Games</h3>
                        <div class="kpi-value"><?php echo $approved_bookings; ?></div>
                    </div>
                    <div class="kpi-icon">✅</div>
                </div>
                <div class="kpi-card revenue">
                    <div class="kpi-info">
                        <h3>Total Revenue</h3>
                        <div class="kpi-value">₦<?php echo number_format($total_revenue); ?></div>
                    </div>
                    <div class="kpi-icon">💰</div>
                </div>
            </div>

            <!-- Table Panel -->
            <div class="panel">
                <?php if ($view === 'users'): ?>
                    <h3 class="panel-title">Registered Users</h3>
                    <?php if (empty($users)): ?>
                        <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No registered users found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Registration Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>#<?php echo $user['id']; ?></td>
                                            <td style="font-weight: 600; color: var(--primary-dark);"><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                <?php elseif ($view === 'settings'): ?>
                    <h3 class="panel-title">⚙️ Settings</h3>
                    <p style="color: var(--text-muted); margin-bottom: 2rem;">Set individual prices for each time slot. Changes apply immediately across the site.</p>

                    <form action="admin_update_settings.php" method="POST" id="settingsForm">
                        <?php echo csrf_field(); ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                            <?php
                            $slot_labels = [
                                '4PM-5PM'   => ['label' => '4:00 PM – 5:00 PM',  'icon' => '☀️'],
                                '5PM-6PM'   => ['label' => '5:00 PM – 6:00 PM',  'icon' => '☀️'],
                                '6PM-7PM'   => ['label' => '6:00 PM – 7:00 PM',  'icon' => '🌅'],
                                '7PM-8PM'   => ['label' => '7:00 PM – 8:00 PM',  'icon' => '🌙'],
                                '8PM-9PM'   => ['label' => '8:00 PM – 9:00 PM',  'icon' => '🌙'],
                                '9PM-10PM'  => ['label' => '9:00 PM – 10:00 PM', 'icon' => '🌙'],
                                '10PM-11PM' => ['label' => '10:00 PM – 11:00 PM','icon' => '🌙'],
                            ];
                            foreach ($slot_labels as $slot => $meta):
                                $price = $slot_prices[$slot] ?? 15000;
                            ?>
                            <div style="background: var(--bg-light); border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 1rem;">
                                <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">
                                    <?php echo $meta['icon'] . ' ' . $meta['label']; ?>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <span style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">₦</span>
                                    <input
                                        type="number"
                                        name="price_<?php echo $slot; ?>"
                                        value="<?php echo $price; ?>"
                                        min="1" step="1" required
                                        style="width: 100%; font-size: 1rem; font-weight: 700; border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 0.4rem 0.6rem; color: var(--primary-dark);"
                                    >
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" id="saveBtn" class="btn btn-primary" style="width: 100%; font-size: 1rem; padding: 0.85rem;">💾 Save All Prices</button>
                    </form>

                    <script>
                        document.getElementById('settingsForm').addEventListener('submit', function() {
                            var btn = document.getElementById('saveBtn');
                            btn.textContent = 'Saving...';
                            btn.disabled = true;
                        });
                    </script>

                <?php elseif ($view === 'calendar'): ?>
                    <h3 class="panel-title">Visual Calendar</h3>
                    
                    <!-- Calendar Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; background: var(--bg-light); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <button type="button" id="prevMonthBtn" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">&larr; Prev</button>
                        <h4 id="calendarMonthYear" style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin: 0;"></h4>
                        <button type="button" id="nextMonthBtn" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Next &rarr;</button>
                    </div>

                    <!-- Custom Grid Table -->
                    <div class="table-responsive">
                        <table style="border: 1px solid var(--border); width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 500px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center; font-weight: 700; width: 14.28%;">Sun</th>
                                    <th style="text-align: center; font-weight: 700; width: 14.28%;">Mon</th>
                                    <th style="text-align: center; font-weight: 700; width: 14.28%;">Tue</th>
                                    <th style="text-align: center; font-weight: 700; width: 14.28%;">Wed</th>
                                    <th style="text-align: center; font-weight: 700; width: 14.28%;">Thu</th>
                                    <th style="text-align: center; font-weight: 700; width: 14.28%;">Fri</th>
                                    <th style="text-align: center; font-weight: 700; width: 14.28%;">Sat</th>
                                </tr>
                            </thead>
                            <tbody id="calendarGridBody">
                                <!-- Generated cells -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Day Details Panel -->
                    <div id="dayDetailsPanel" class="panel" style="display: none; border-top-color: var(--accent-yellow); margin-top: 2rem; padding: 1.5rem; box-shadow: var(--shadow-sm);">
                        <h4 id="dayDetailsTitle" style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"></h4>
                        <div id="dayDetailsContent"></div>
                    </div>

                <?php else: ?>
                    <h3 class="panel-title">Bookings Management</h3>
                    <?php if (empty($bookings)): ?>
                        <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No bookings found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>User Details</th>
                                        <th>Booking Date</th>
                                        <th>Time Slot</th>
                                        <th>Payment Proof</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 600; color: var(--primary-dark);"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($booking['user_phone']); ?></div>
                                            </td>
                                            <td style="font-weight: 600;"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                                            <td>
                                                 <button type="button" class="btn btn-secondary zoomable-img" data-src="<?php echo htmlspecialchars($booking['payment_proof']); ?>" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; white-space: nowrap;">🖼️ View Receipt</button>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $booking['status']; ?>">
                                                    <?php echo htmlspecialchars($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <form action="admin_action.php" method="POST" style="display:inline;">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; border-radius: var(--radius-sm); margin-right: 5px;">Approve</button>
                                                    </form>
                                                    <form action="admin_action.php" method="POST" style="display:inline;">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; border-radius: var(--radius-sm);">Reject</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">No actions</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
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
        // Set raw bookings array for Javascript
        <?php if ($view === 'calendar'): ?>
        const rawBookings = <?php echo json_encode($calendar_bookings); ?>;
        <?php else: ?>
        const rawBookings = [];
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', function() {
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

            // Visual Calendar Logic
            if (document.getElementById('calendarGridBody')) {
                let today = new Date();
                let currentMonth = today.getMonth();
                let currentYear = today.getFullYear();

                const months = [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];

                const calendarMonthYear = document.getElementById('calendarMonthYear');
                const calendarGridBody = document.getElementById('calendarGridBody');
                const prevMonthBtn = document.getElementById('prevMonthBtn');
                const nextMonthBtn = document.getElementById('nextMonthBtn');
                const dayDetailsPanel = document.getElementById('dayDetailsPanel');
                const dayDetailsTitle = document.getElementById('dayDetailsTitle');
                const dayDetailsContent = document.getElementById('dayDetailsContent');

                function renderCalendar(month, year) {
                    calendarGridBody.innerHTML = '';
                    calendarMonthYear.textContent = `${months[month]} ${year}`;

                    let firstDay = new Date(year, month, 1).getDay();
                    let daysInMonth = new Date(year, month + 1, 0).getDate();

                    let date = 1;
                    for (let i = 0; i < 6; i++) {
                        let row = document.createElement('tr');
                        let rowHasContent = false;

                        for (let j = 0; j < 7; j++) {
                            let cell = document.createElement('td');
                            cell.style.verticalAlign = 'top';
                            cell.style.height = '100px';
                            cell.style.border = '1px solid var(--border)';
                            cell.style.padding = '0.5rem';
                            cell.style.cursor = 'default';

                            if (i === 0 && j < firstDay) {
                                cell.innerHTML = '';
                            } else if (date > daysInMonth) {
                                cell.innerHTML = '';
                            } else {
                                rowHasContent = true;
                                let cellDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;

                                let dayNumLabel = document.createElement('div');
                                dayNumLabel.style.fontWeight = 'bold';
                                dayNumLabel.style.marginBottom = '0.5rem';
                                dayNumLabel.style.color = 'var(--text-dark)';
                                dayNumLabel.textContent = date;

                                let todayDateStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                                if (cellDateStr === todayDateStr) {
                                    dayNumLabel.style.background = 'var(--primary)';
                                    dayNumLabel.style.color = '#0b130f';
                                    dayNumLabel.style.borderRadius = '50%';
                                    dayNumLabel.style.width = '24px';
                                    dayNumLabel.style.height = '24px';
                                    dayNumLabel.style.display = 'flex';
                                    dayNumLabel.style.alignItems = 'center';
                                    dayNumLabel.style.justifyContent = 'center';
                                }

                                cell.appendChild(dayNumLabel);

                                let dayBookings = rawBookings.filter(b => b.booking_date === cellDateStr);

                                if (dayBookings.length > 0) {
                                    cell.style.cursor = 'pointer';
                                    cell.style.background = 'rgba(25, 195, 125, 0.03)';
                                    
                                    cell.addEventListener('mouseenter', () => cell.style.background = 'rgba(25, 195, 125, 0.08)');
                                    cell.addEventListener('mouseleave', () => cell.style.background = 'rgba(25, 195, 125, 0.03)');

                                    let targetDateStr = cellDateStr;
                                    let bookingsList = dayBookings;
                                    cell.addEventListener('click', () => {
                                        showDayDetails(targetDateStr, bookingsList);
                                    });

                                    dayBookings.forEach(b => {
                                        let badge = document.createElement('div');
                                        badge.style.fontSize = '0.75rem';
                                        badge.style.padding = '2px 6px';
                                        badge.style.borderRadius = '4px';
                                        badge.style.marginBottom = '2px';
                                        badge.style.fontWeight = '600';
                                        badge.style.overflow = 'hidden';
                                        badge.style.textOverflow = 'ellipsis';
                                        badge.style.whiteSpace = 'nowrap';

                                        if (b.status === 'approved') {
                                            badge.style.background = '#d1fae5';
                                            badge.style.color = '#065f46';
                                            badge.textContent = `✔ ${b.time_slot} - ${b.user_name}`;
                                        } else {
                                            badge.style.background = '#fef3c7';
                                            badge.style.color = '#92400e';
                                            badge.textContent = `⏳ ${b.time_slot} - ${b.user_name}`;
                                        }
                                        cell.appendChild(badge);
                                    });
                                }
                                date++;
                            }
                            row.appendChild(cell);
                        }
                        if (rowHasContent) {
                            calendarGridBody.appendChild(row);
                        }
                    }
                }

                function showDayDetails(dateStr, bookings) {
                    let formatted = new Date(dateStr).toLocaleDateString('en-US', {
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                    });
                    dayDetailsTitle.textContent = `Reservations on ${formatted}`;
                    dayDetailsContent.innerHTML = '';
                    dayDetailsPanel.style.display = 'block';

                    let listTable = document.createElement('div');
                    listTable.className = 'table-responsive';

                    let html = `
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Phone</th>
                                    <th>Slot</th>
                                    <th>Proof</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    bookings.forEach(b => {
                        let actionHtml = '';
                        if (b.status === 'pending') {
                            const csrfToken = '<?php echo htmlspecialchars(generate_csrf_token()); ?>';
                            actionHtml = `
                                <form action="admin_action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                                    <input type="hidden" name="id" value="${b.id}">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; border-radius: var(--radius-sm); margin-right: 5px;">Approve</button>
                                </form>
                                <form action="admin_action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                                    <input type="hidden" name="id" value="${b.id}">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; border-radius: var(--radius-sm);">Reject</button>
                                </form>
                            `;
                        } else {
                            actionHtml = `<span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">No actions</span>`;
                        }

                        html += `
                            <tr>
                                <td style="font-weight: 600; color: var(--primary-dark);">${escapeHtml(b.user_name)}</td>
                                <td>${escapeHtml(b.user_phone)}</td>
                                <td style="font-weight: 600;">${escapeHtml(b.time_slot)}</td>
                                <td>
                                    <button type="button" class="btn btn-secondary zoomable-img-dynamic" data-src="${escapeHtml(b.payment_proof)}" style="padding: 0.3rem 0.6rem; font-size: 0.78rem; white-space: nowrap;">🖼️ View</button>
                                </td>
                                <td>
                                    <span class="badge badge-${b.status}">${b.status}</span>
                                </td>
                                <td>${actionHtml}</td>
                            </tr>
                        `;
                    });

                    html += `
                            </tbody>
                        </table>
                    `;

                    listTable.innerHTML = html;
                    dayDetailsContent.appendChild(listTable);

                    // Add zoomable listener to dynamic buttons
                    const dynamicImgs = listTable.querySelectorAll('.zoomable-img-dynamic');
                    dynamicImgs.forEach(el => {
                        el.addEventListener('click', function() {
                            modal.style.display = "block";
                            modalImg.src = this.dataset.src || this.src;
                        });
                    });
                }

                function escapeHtml(str) {
                    return str
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }

                prevMonthBtn.addEventListener('click', function() {
                    currentMonth--;
                    if (currentMonth < 0) {
                        currentMonth = 11;
                        currentYear--;
                    }
                    renderCalendar(currentMonth, currentYear);
                    dayDetailsPanel.style.display = 'none';
                });

                nextMonthBtn.addEventListener('click', function() {
                    currentMonth++;
                    if (currentMonth > 11) {
                        currentMonth = 0;
                        currentYear++;
                    }
                    renderCalendar(currentMonth, currentYear);
                    dayDetailsPanel.style.display = 'none';
                });

                // Render on init
                renderCalendar(currentMonth, currentYear);
            }
        });
    </script>
</body>
</html>
