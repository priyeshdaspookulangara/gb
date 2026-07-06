<?php
// dashboards/ui_preview.php
session_start();
// Mock session data for preview
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'preview_user';
$_SESSION['role'] = 'admin';
$_SESSION['full_name'] = 'Demo Admin';
$_SESSION['profile_pic'] = null;

// The layout files will include auth_helper which defines these.
// We just need to make sure the session is set before they are called.

require_once '../layouts/header.php';
?>

<!-- Hospital Stats Style Cards -->
<div class="card-group-row">
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-content">
            <h3>Total Users</h3>
            <p>1,284</p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-content">
            <h3>Active Bookings</h3>
            <p>856</p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-indian-rupee-sign"></i></div>
        <div class="stat-content">
            <h3>Total Revenue</h3>
            <p>₹30,816,000</p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-file-signature"></i></div>
        <div class="stat-content">
            <h3>Pending KYC</h3>
            <p style="color: var(--warning-color);">12</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="glass-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0 gold-text">System Actions</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="process_salaries.php" class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn-gold">Process Monthly Salaries</button>
                    <small class="text-muted" style="margin-left: 15px;">Distribute rank incentives to all qualified promoters for the current month.</small>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="glass-card">
            <div class="card-header">
                <h4 class="m-0 gold-text">Automation Status</h4>
            </div>
            <div style="margin-top: 15px;">
                <div style="border-bottom: 1px solid var(--glass-border); padding: 10px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600;">Daily Reward Points</span>
                        <span class="status approved" style="font-size: 9px; padding: 2px 5px;">SUCCESS</span>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                        Today, 12:00 • 0.042s
                    </div>
                </div>
                <div style="border-bottom: 1px solid var(--glass-border); padding: 10px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600;">Monthly Salary Cron</span>
                        <span class="status approved" style="font-size: 9px; padding: 2px 5px;">SUCCESS</span>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                        01 Jan, 00:00 • 0.156s
                    </div>
                </div>
                <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button class="btn-primary" style="font-size: 10px; text-align: center; padding: 8px; border:none; cursor:pointer;">Run Daily Points</button>
                    <button class="btn-primary" style="font-size: 10px; text-align: center; padding: 8px; border:none; cursor:pointer;">Run Salary Cron</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h4 class="m-0">Recent Activity Preview</h4>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Type</th>
                    <th>Gross</th>
                    <th>Net Credit</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>John Smith</strong></td>
                    <td><span class="status pending" style="background:#f1f5f9; color: #475569;">LEVEL INCENTIVE</span></td>
                    <td class="text-muted">Rs. 1,000.00</td>
                    <td><strong class="text-success">Rs. 850.00</strong></td>
                    <td class="text-muted">Today, 10:45</td>
                </tr>
                <tr>
                    <td><strong>Sarah Jones</strong></td>
                    <td><span class="status pending" style="background:#f1f5f9; color: #475569;">REFERRAL INCENTIVE</span></td>
                    <td class="text-muted">Rs. 2,000.00</td>
                    <td><strong class="text-success">Rs. 1,700.00</strong></td>
                    <td class="text-muted">Today, 09:30</td>
                </tr>
                <tr>
                    <td><strong>Mike Brown</strong></td>
                    <td><span class="status canceled" style="background:#fee2e2; color: #991b1b;">WITHDRAWAL</span></td>
                    <td class="text-muted">Rs. 5,000.00</td>
                    <td><strong class="text-danger">-Rs. 5,000.00</strong></td>
                    <td class="text-muted">Yesterday, 18:15</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
