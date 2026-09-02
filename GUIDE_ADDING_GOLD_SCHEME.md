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

## 💡 Practical Use Cases & Examples

Below are 3 concrete real-world use cases showing how to configure different types of gold schemes for various investor demographics:

### Use Case 1: "Starter Express" Plan (Short-Term / Entry Level)
Designed for new retail buyers wanting a low-barrier trial plan before making larger bookings.

* **Form Fields Input:**
  * **Scheme Code:** `GS18K`
  * **Scheme Name:** `Starter 6-Month Gold Scheme`
  * **Deposit (Rs):** `18000`
  * **Maturity (Rs):** `30000`
  * **Total Duration (Months):** `6`
  * **Milestone 1 (Month):** `2` | **M1 Amount (Rs):** `8000`
  * **Milestone 2 (Month):** `4` | **M2 Amount (Rs):** `10000`
  * **Description:** `A compact 6-month maturation plan tailored for new investors and first-time bullion buyers.`

* **SQL Snippet:**
  ```sql
  INSERT INTO gold_schemes (scheme_code, scheme_name, deposit_amount, maturity_amount, duration_months, milestone_1_month, milestone_1_amount, milestone_2_month, milestone_2_amount, description)
  VALUES ('GS18K', 'Starter 6-Month Gold Scheme', 18000, 30000, 6, 2, 8000, 4, 10000, 'A compact 6-month maturation plan tailored for new investors and first-time bullion buyers.');
  ```

---

### Use Case 2: "Standard Bullion Card" Plan (Core Flagship Scheme)
The standard MLM matrix scheme for regular promoters and investors.

* **Form Fields Input:**
  * **Scheme Code:** `GS36K`
  * **Scheme Name:** `Standard 11-Month Plan`
  * **Deposit (Rs):** `36000`
  * **Maturity (Rs):** `66000`
  * **Total Duration (Months):** `11`
  * **Milestone 1 (Month):** `4` | **M1 Amount (Rs):** `16000`
  * **Milestone 2 (Month):** `8` | **M2 Amount (Rs):** `20000`
  * **Description:** `Our signature 11-month gold bullion advance booking scheme with dual milestone redemption vouchers.`

* **SQL Snippet:**
  ```sql
  INSERT INTO gold_schemes (scheme_code, scheme_name, deposit_amount, maturity_amount, duration_months, milestone_1_month, milestone_1_amount, milestone_2_month, milestone_2_amount, description)
  VALUES ('GS36K', 'Standard 11-Month Plan', 36000, 66000, 11, 4, 16000, 8, 20000, 'Our signature 11-month gold bullion advance booking scheme with dual milestone redemption vouchers.');
  ```

---

### Use Case 3: "High-Yield Collector" Plan (High-Value / Premium)
Targeted at serious bullion investors and high-net-worth clients seeking high yield over 15 months.

* **Form Fields Input:**
  * **Scheme Code:** `GS72K`
  * **Scheme Name:** `Premium 15-Month Gold Scheme`
  * **Deposit (Rs):** `72000`
  * **Maturity (Rs):** `150000`
  * **Total Duration (Months):** `15`
  * **Milestone 1 (Month):** `5` | **M1 Amount (Rs):** `35000`
  * **Milestone 2 (Month):** `8` | **M2 Amount (Rs):** `50000`
  * **Description:** `High-yield 15-month maturation plan designed for serious bullion collectors with maximum redemption value.`

* **SQL Snippet:**
  ```sql
  INSERT INTO gold_schemes (scheme_code, scheme_name, deposit_amount, maturity_amount, duration_months, milestone_1_month, milestone_1_amount, milestone_2_month, milestone_2_amount, description)
  VALUES ('GS72K', 'Premium 15-Month Gold Scheme', 72000, 150000, 15, 5, 35000, 8, 50000, 'High-yield 15-month maturation plan designed for serious bullion collectors with maximum redemption value.');
  ```

---

## 🎯 Strategic Tips, Tricks & Best Practices for Maximum Business Profitability

To maximize corporate profitability, cash flow stability, and promoter conversion when introducing new gold schemes, follow these industry best practices:

### 1. 💰 Working Capital & Cash Flow Retention (Milestone Structuring)
* **Keep Early Milestones Capped:** Ensure `Milestone 1 Amount` + `Milestone 2 Amount` remains less than or equal to the initial `Deposit Amount`. This prevents negative working capital early in the scheme lifecycle.
* **Voucher Redemptions vs Cash Payouts:** Issue milestone claims as **Gold Purchase Vouchers** redeemable for physical jewelry/coins or store points rather than direct cash withdrawals. This keeps capital within the Luxe Gold ecosystem.

### 2. 📈 Hedging Against Gold Spot Price Volatility
* **Physical Gold Hedging Strategy:** Whenever a high-value scheme booking is activated, use 70-80% of the deposit to immediately acquire physical gold bullion contracts or lock spot prices with corporate suppliers. This protects corporate margins against sudden gold price surges before final maturation.

### 3. 🤝 Commission Margin Cushioning (MLM Integration)
* **Account for 8-Level Unilevel Commissions:** In the system, an active booking distributes up to ₹2,000 in Direct Referral Bonus + up to ₹3,050 across 8 MLM levels (Level 1: ₹1,000 down to Level 8: ₹100).
* **Formula for Net Corporate Margin:**
  $$\text{Net Margin} = \text{Deposit Amount} - (\text{MLM Level Commissions} + \text{Direct Referral Incentive}) - \text{Hedging Cost}$$
  Ensure the deposit amount provides at least a 15-20% buffer above total commission liabilities.

### 4. 🚀 Tiered "Good-Better-Best" Product Laddering
* **Offer 3 Active Schemes Simultaneously:** Maintain a low entry plan (`GS18K`), a standard flagship plan (`GS36K`), and a high-yield plan (`GS72K`).
* **Promoter Upselling Motivation:** Promoters naturally push the `GS72K` plan to downlines because higher card deposits generate higher team sales volume toward rank upgrades (LCE → BCE → EPE) and 10-month company salary qualifications.

### 5. 🎨 High-Converting Branding & Visual Assets
* **Card Image Optimization:** Use high-contrast images featuring gold ingots, luxury jewelry, or branded bullion coins with dark/gold aesthetics (`#16000D` background, `#E2B747` gold borders). Clean visuals on `gold-plans.php` can improve sign-up conversions by over 40%.
* **Clear Benefit Bullet Points:** Highlight non-cash perks in the `description` field, such as *"Includes Earn Buy Reward Points + Free Jewelry Gift Voucher upon Month 4 Maturation"*.

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
