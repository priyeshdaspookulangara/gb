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
