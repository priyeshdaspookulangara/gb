# Gold Bullion Advance Booking & MLM System

This is a complete backend management and MLM portal for Luxe Gold & Diamonds and Earn Buy Marketing.

## System Roles & Responsibilities

### A. Super Admin (Corporate Control)
*   **User Management**: Approve or reject new registrations and KYC documents.
*   **Global Overview**: Monitor total sales, revenue, and gold liabilities.
*   **Incentive Processing**: Trigger the "Monthly Salary" process to distribute rank maintenance incentives to qualified promoters.
*   **Payout Control**: Review and approve withdrawal requests from the e-wallet.

### B. Earn Buy Promoter (The Affiliate)
*   **Network Building**: Use the personal referral link to recruit new Promoters and Customers.
*   **Downline Tracking**: Visualize the 8-level unilevel tree to monitor team performance and ranks.
*   **Income Management**: Track Direct Referral Incentives (Rs. 2,000), Level Incentives, and Monthly Salaries.
*   **E-Wallet**: View balances and request withdrawals of earned commissions.

### C. Customer (The Investor)
*   **Advance Booking**: Deposit Rs. 36,000 to purchase a Gold Bullion Card.
*   **Maturity Tracking**: Use the animated progress bar to monitor investment growth towards the Rs. 66,000 target.
*   **Milestone Redemptions**:
    *   **Month 4**: Claim Rs. 16,000 gold voucher.
    *   **Month 8**: Claim Rs. 20,000 gold voucher.
    *   **Month 11**: Full maturation reach.
*   **Ledger**: Keep track of payments and redemption history.

## Technical Getting Started

### Database Setup
1. Import `database.sql` into your MySQL server.
2. Update database credentials in `config/db.php`.

### Default Admin Access
- **URL**: `/login.php`
- **Username**: `admin`
- **Password**: `admin123`

### Registration
- Users can sign up via `/register.php`.
- Promoters can share their referral link: `/register.php?ref=USERNAME`.

## Project Structure
- `config/`: Database connection.
- `auth/`: Authentication helpers and session management.
- `includes/`: Core business logic (Commission Engine, Salary Processor).
- `dashboards/`: Role-specific user interfaces.
- `assets/`: CSS styles (Glassmorphism & Brand Colors).
