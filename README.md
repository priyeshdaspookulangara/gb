# Gold Bullion Advance Booking & MLM System

This is a complete backend management and MLM portal for Luxe Gold & Diamonds and Earn Buy Marketing.

## Features
- **8-Level Unilevel MLM Engine**: Automatic commission distribution and team sales tracking.
- **Dynamic Ranking**: Automatic promoter tier upgrades based on performance.
- **Gold Maturity Tracker**: Animated progress tracking for customers over 11 months.
- **E-Wallet & Ledger**: Full transaction history and payout tracking.
- **Premium UI**: Modern Glassmorphic design with gold accents.

## Getting Started

### Database Setup
1. Import `database.sql` into your MySQL server.
2. Update database credentials in `config/db.php`.

### Default Access
- **Super Admin Dashboard**
  - **URL**: `/login.php`
  - **Username**: `admin`
  - **Password**: `admin123`

### Creating Other Roles
- **Promoter / Customer**
  - Go to `/register.php`
  - Select your role during registration.
  - You can use the `admin` username as the sponsor if needed.

## User Journeys
1. **Promoters**: Register, get your referral link, and build your downline. Earn direct commissions (Rs. 2,000) and level incentives.
2. **Customers**: Register, purchase a Gold Bullion Advance Booking Card (Rs. 36,000), and track your maturation to Rs. 66,000 over 11 months.
3. **Admin**: Approve users, monitor global sales, and trigger monthly salary processing.
