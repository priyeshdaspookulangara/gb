# Step-by-Step Guide: Adding a New Gold Scheme in Luxe Gold System

This comprehensive tutorial explains how to create, configure, and manage Gold Schemes in the **Luxe Gold & Earn Buy Marketing** Advance Booking & MLM Management System.

---

## 📌 Overview & Architecture

In the Luxe Gold system, **Gold Schemes** represent the core financial products available for advance gold bullion booking. Each scheme defines:
- The initial deposit amount paid by the customer.
- The overall maturation value and duration (in months).
- Phased milestone redemption thresholds (Month 1 & Month 2 vouchers).
- Visual branding and descriptions displayed across public and portal interfaces.

When a new active scheme is created, it is automatically integrated into:
1. **Public Gold Plans Page** (`gold-plans.php`) for public visitors.
2. **User Registration Form** (`register.php`) in the "Select Plan" dropdown.
3. **Customer Booking Portal** (`dashboards/new_booking.php`) for existing users creating additional bookings.

---

## 🛠️ Method 1: Adding a Scheme via Admin Dashboard (Recommended)

### Step 1: Log in to the Admin Dashboard
1. Open your web browser and navigate to the portal login page (`/login.php`).
2. Log in using your Super Admin credentials:
   - **Username:** `admin`
   - **Password:** `admin123` *(or your custom admin password)*

### Step 2: Navigate to Gold Schemes Management
1. In the left navigation sidebar, click on **Gold Schemes** (or visit `/dashboards/admin_schemes.php`).
2. You will see the **Add New Scheme** form on the left and the list of **Active Schemes** on the right.

### Step 3: Complete the "Add New Scheme" Form
Fill in the following fields accurately:

| Field Name | Type | Example Input | Explanation & Requirements |
| :--- | :--- | :--- | :--- |
| **Scheme Code** | Text | `GS50K` | Unique identifier code for the scheme (e.g., `GS18K`, `GS36K`, `GS50K`, `GS72K`). |
| **Scheme Name** | Text | `Special 12-Month Plan` | Full display title shown to users and administrators. |
| **Deposit (Rs)** | Number | `50000` | The advance booking payment amount (in ₹) required to activate the plan. |
| **Maturity (Rs)** | Number | `90000` | Total monetary value (in ₹) at full maturation. |
| **Total Duration (Months)** | Number | `12` | Duration of the scheme in months (e.g., `6`, `11`, `12`, `15`). |
| **Milestone 1 (Month)** | Number | `4` | The month number at which the customer becomes eligible for the 1st milestone voucher (Default: 4). |
| **M1 Amount (Rs)** | Number | `20000` | Value (in ₹) of the 1st milestone redemption voucher. |
| **Milestone 2 (Month)** | Number | `8` | The month number at which the customer becomes eligible for the 2nd milestone voucher (Default: 8). |
| **M2 Amount (Rs)** | Number | `30000` | Value (in ₹) of the 2nd milestone redemption voucher. |
| **Description** | Text Area | `12-month advance bullion plan with double milestone vouchers.` | Detailed summary explaining plan highlights and benefits. |
| **Representational Image** | File Upload | `scheme_banner.png` | Optional cover image. Supported formats: **.JPG, .JPEG, .PNG, .WEBP**. Max size recommended: under 2MB. |

### Step 4: Submit and Verify
1. Click the **Create Scheme** button at the bottom of the form.
2. A green notification **"Scheme created successfully."** will appear.
3. Verify that your new scheme appears under the **Active Schemes** table on the right side of the dashboard.

---

## 🗄️ Method 2: Adding a Scheme via SQL Query (Database Direct)

If you have direct database access (via MySQL CLI, phpMyAdmin, or cPanel Database Tools), you can insert a scheme directly into the `gold_schemes` table using SQL.

```sql
USE gold_bullion_system;

INSERT INTO gold_schemes (
    scheme_code,
    scheme_name,
    deposit_amount,
    maturity_amount,
    duration_months,
    milestone_1_month,
    milestone_1_amount,
    milestone_2_month,
    milestone_2_amount,
    description,
    scheme_image,
    is_active
) VALUES (
    'GS50K',
    'Special 12-Month Plan',
    50000.00,
    90000.00,
    12,
    4,
    20000.00,
    8,
    30000.00,
    '12-month advance bullion plan with double milestone vouchers.',
    NULL, -- Or image filename such as 'scheme_1700000000.png'
    1
);
```

---

## 🔄 System Verification & Testing

After creating a new scheme, perform these verification steps to confirm proper integration across the application:

1. **Public Showcase Page (`gold-plans.php`):**
   - Visit `/gold-plans.php` in an incognito window.
   - Confirm that the new plan card displays the correct title, code, price deposit, maturity, milestones, and cover image.

2. **User Registration Page (`register.php`):**
   - Go to `/register.php`.
   - Inspect the **"Select Plan"** dropdown menu and confirm the new plan is listed.

3. **Customer Booking Portal (`dashboards/new_booking.php`):**
   - Log in as a customer account (e.g. `customer01`).
   - Click **Bookings** -> **New Booking**.
   - Verify that the card for the new scheme is clickable and populates the payment options (Gateway, ePin, QR Code).

4. **Deactivation / Deleting a Scheme:**
   - To temporarily disable a scheme without deleting historical bookings, click **Deactivate** next to the scheme in `dashboards/admin_schemes.php`.
   - This sets `is_active = 0`, hiding it from registration and new booking selections while preserving existing customer records.

---

## ❓ Frequently Asked Questions & Troubleshooting

- **Image Upload Fails / "Failed to move uploaded file":**
  Ensure the directory `/uploads/schemes/` exists and has write permissions (e.g. `chmod 775` or `chmod 777`).

- **Invalid File Type Error:**
  Only uploaded files with extensions `.jpg`, `.jpeg`, `.png`, or `.webp` are accepted by the system file validator.

- **CSRF Token Error on Form Submit:**
  Ensure your browser session is active. If your session timed out, refresh the page and re-submit the form.

- **Changes Not Reflecting on Public Page:**
  Ensure `is_active` is set to `1` (true). Only active schemes are queried by `gold-plans.php`, `register.php`, and `new_booking.php`.
