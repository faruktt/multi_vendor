# 🛍️ Multi-Vendor E-Commerce, POS & Warehouse ERP System

[![Laravel](https://img.shields.io/badge/Laravel-12%2F13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Frontend](https://img.shields.io/badge/Frontend-Vite%20%7C%20Blade-38B2AC?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

An enterprise-grade, all-in-one **Multi-Vendor E-Commerce, Retail POS, Central Warehouse ERP & Reseller Network Platform** built with Laravel. Designed to manage end-to-end e-commerce operations—from customer storefront purchases, supplier onboarding, and reseller networks to warehouse distribution, in-store POS billing, telephonic IP PBX verification, automated courier logistics, and fraud prevention.

---

## 📑 Table of Contents

- [Core Portals & User Roles](#-core-portals--user-roles)
- [Key Features & Modules](#-key-features--modules)
  - [1. Customer Storefront & Account Portal](#1-customer-storefront--account-portal)
  - [2. Super Admin Panel & Enterprise ERP](#2-super-admin-panel--enterprise-erp)
  - [3. Central Warehouse & Inventory ERP](#3-central-warehouse--inventory-erp)
  - [4. Branch Point of Sale (POS)](#4-branch-point-of-sale-pos)
  - [5. Multi-Vendor / Supplier Portal](#5-multi-vendor--supplier-portal)
  - [6. Dropshipping & Reseller Portal](#6-dropshipping--reseller-portal)
  - [7. Moderator & Telecaller Work Management](#7-moderator--telecaller-work-management)
  - [8. Logistics, Courier & Delivery Integrations](#8-logistics-courier--delivery-integrations)
  - [9. IP PBX Telephony & Order Verification](#9-ip-pbx-telephony--order-verification)
  - [10. Courier Delivery History & Fraud Detection](#10-courier-delivery-history--fraud-detection)
  - [11. Live Chat & Customer Communication](#11-live-chat--customer-communication)
  - [12. Finance, Commission & Payouts](#12-finance-commission--payouts)
- [System Architecture & Tech Stack](#-system-architecture--tech-stack)
- [Installation & Local Setup](#-installation--local-setup)
- [Directory Structure](#-directory-structure)
- [License](#-license)

---

## 🌟 Core Portals & User Roles

| Portal | URL Route | Description |
|---|---|---|
| **Public Storefront** | `/` or `/shop` | Modern responsive storefront for retail customers |
| **Customer Portal** | `/customer/login` | Order history, order tracking, profile, live chat |
| **Super Admin Panel** | `/admin` | Central governance, reports, settings, branches, staff |
| **Central Warehouse** | `/admin/warehouse/dashboard` | Master product catalog, purchases, stock transfers |
| **Branch POS / Store** | `/admin/branch/{id}/pos` | Retail counter billing, barcode scanning, branch stock |
| **Supplier Portal** | `/supplier/login` | Multi-vendor dashboard, product upload, orders, payout |
| **Reseller Portal** | `/reseller/login` | Dropshipper order management, profit margin, withdrawal |
| **Moderator Portal** | `/moderator/login` | Shift punch in/out, order verification, work sessions |

---

## 🚀 Key Features & Modules

### 1. Customer Storefront & Account Portal
- **Modern Responsive Design**: Dynamic home page with hero carousel banners, promotional badges, and featured categories.
- **Product Discovery**: Fast search, category filtering, product variant selectors (Color, Size), pricing display, and stock indicators.
- **Flash Sales & Deals**: Real-time flash sale countdown timers and discounted campaign pricing.
- **Cart & Checkout**: Interactive slide-out cart drawer, coupon code validation, and dynamic delivery charge calculation by location (Inside/Outside Dhaka / Sub-districts).
- **Public Order Tracking**: Real-time order progress lookup via invoice number and phone number (`/shop/track`).
- **Dedicated Storefronts**: Custom store pages for individual suppliers (`/supplier-store/{id}`).
- **Customer Dashboard**: Track order histories, download invoice receipts, update delivery addresses, and chat with support.

### 2. Super Admin Panel & Enterprise ERP
- **Multi-Branch Management**: Add, update, and manage multiple retail branches with separate inventory and staff access.
- **Comprehensive Analytics**: Dashboard cards for total sales, revenue, profit, low-stock warnings, purchase volume, and pending orders.
- **Dynamic Homepage Control**: Admin banner management and homepage content block reordering.
- **Coupons & Discounts**: Percentage or fixed amount coupons with expiry dates, minimum purchase requirements, and usage limits.
- **Role-Based Access Control (RBAC)**: Fine-grained permissions using `spatie/laravel-permission` across admins, managers, cashiers, and warehouse operators.
- **Audit Activity Logs**: Detailed activity tracking logging every model change, deletion, and staff action with IP addresses.
- **Traffic Analytics**: Live tracking of visitor pages, session hits, and referrer analytics.

### 3. Central Warehouse & Inventory ERP
- **Master Catalog Management**: Create and maintain all products centrally with multi-image upload, SEO slugs, and categories.
- **Variant Attributes**: Flexible management of dynamic product variations (Colors, Sizes, SKUs, and variant pricing).
- **Purchases & Supplier Payables**: Manage purchase orders from wholesalers, partial payment tracking, and purchase returns.
- **Inter-Branch Stock Transfers**: Transfer inventory from the central warehouse to specific retail branches with status tracking.
- **Stock Movement Ledger**: Complete audit trail of inventory in/out for every product and variant.
- **Barcode Generation**: Instant thermal barcode sticker generation and bulk printing (`picqer/php-barcode-generator`).

### 4. Branch Point of Sale (POS)
- **High-Speed Counter Billing**: Fast product search by name, SKU, or direct hardware barcode scanner input.
- **Customer Management**: Instant customer lookup or quick new customer addition right from the billing screen.
- **Multiple Payment Methods**: Support for Cash, bKash, Nagad, Card, and split payments.
- **Thermal Receipt & Bulk Invoicing**: Professional printable 80mm thermal receipts and standard A4 invoices.
- **Sales Returns & Exchanges**: Easy processing of customer returns and partial refunds with automated stock restoration.
- **Daily Cash Register & Reports**: Branch sales summaries, profit margins, and inventory counts.

### 5. Multi-Vendor / Supplier Portal
- **Vendor Onboarding**: Supplier self-registration and profile setup with admin approval workflow.
- **Vendor Product Submission**: Vendors can submit products with images and wholesale pricing for admin review and approval.
- **Order Fulfillment**: Dedicated dashboard displaying vendor-specific orders, pack slips, and customer shipping labels.
- **Commission Management**: Configurable commission rates per vendor or product category.
- **Payout & Withdrawal Requests**: Wallet balance tracking with automated withdrawal requests to Bank or Mobile Banking (bKash/Nagad/Rocket).

### 6. Dropshipping & Reseller Portal
- **Reseller Network**: Register as a reseller to sell products without holding inventory.
- **Custom Profit Margins**: Resellers place orders with custom retail prices for end-customers; system calculates net profit automatically.
- **Reseller Cart & Order Submission**: Multi-item cart dedicated for reseller order booking with end-customer shipping details.
- **Profit Wallet & Withdrawals**: Live tracking of approved, delivered, and pending commission earnings with withdrawal requests.

### 7. Moderator & Telecaller Work Management
- **Shift & Session Tracking**: Clock-in and clock-out (`work/start`, `work/stop`, `work/end`) with active session timers.
- **Daily Work Logs**: Log order confirmation calls, customer queries, and activities throughout the shift.
- **Performance Reports**: Admin visibility into moderator order verification metrics, work hours, and delivery success rates.
- **Salary / Commission Payout**: Automated calculation of moderator commissions based on completed and delivered orders.

### 8. Logistics, Courier & Delivery Integrations
- **Steadfast & Pathao Courier Support**: Native integration with leading courier APIs in Bangladesh.
- **One-Click Dispatch**: Direct booking of orders from the sales screen to couriers without manual data entry.
- **Automated Consignment Tracking**: Track delivery statuses (In-Transit, Delivered, Returned) directly within the admin dashboard.
- **Courier Delivery History Check (`bd-courier-check`)**: Verify customer phone number success rates across courier services before dispatching.

### 9. IP PBX Telephony & Order Verification
- **096XX Cloud PBX Integration**: Integrated IP telephony support for telecallers and order verification teams.
- **One-Click Dialing**: Click to call customers directly from order details to confirm deliveries.
- **Webhook Handlers**: Automatic call duration logging, recording attachments, and status updates via API webhooks.
- **Call Notes**: Moderators can log call notes and customer instructions linked directly to sales records.

### 10. Courier Delivery History & Fraud Detection
- **Fake Order Prevention**: Automated fraud check tool evaluating customer phone numbers against delivery histories.
- **Return Risk Mitigation**: Identifies customers with high return rates or non-deliveries to prevent courier return losses.
- **Custom Blocklists**: Block fraudulent phone numbers, IP addresses, or suspicious accounts.

### 11. Live Chat & Customer Communication
- **Real-Time Customer Messaging**: In-store live chat widget allowing visitors to communicate with admin support.
- **Admin Chat Center**: Unread message badges, active conversation lists, and fast message response interface.

### 12. Finance, Commission & Payouts
- **Income & Expense Tracking**: Categorized expense entries (rent, utilities, salaries, marketing) alongside sales revenue.
- **Staff Commission System**: Configurable commission incentives for sales executives and phone operators.
- **Supplier & Reseller Settlement**: Comprehensive accounting for vendor balance payouts and reseller margins.
- **Financial Reports**: Date-filtered balance sheets, revenue breakdowns, and net profit estimations.

---

## 🛠️ System Architecture & Tech Stack

- **Backend Framework**: [Laravel 12 / 13](https://laravel.com)
- **Language**: PHP 8.3+
- **Database**: MySQL / MariaDB
- **Frontend / UI**: Laravel Blade Templates, Vanilla CSS, Vite, JavaScript
- **API Authentication**: Laravel Sanctum (Token-based REST APIs)
- **Authorization**: Spatie Laravel Permission (`spatie/laravel-permission`)
- **Barcode Engine**: Picqer PHP Barcode Generator (`picqer/php-barcode-generator`)
- **Job Queues & Scheduler**: Laravel Queues & Background Workers

---

## ⚙️ Installation & Local Setup

### Prerequisites
- PHP >= 8.3 with extensions: `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`
- Composer 2.x
- Node.js >= 18.x & NPM
- MySQL Server >= 8.0

### Step-by-Step Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/faruktt/multi_vendor.git
   cd multi_vendor
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**:
   ```bash
   npm install
   ```

4. **Configure Environment File**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**:
   Open `.env` and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=multi_vendor
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

7. **Link Storage Directory**:
   ```bash
   php artisan storage:link
   ```

8. **Build Frontend Assets & Start Servers**:
   ```bash
   # Development (Run Vite & Laravel concurrently)
   composer run dev

   # Or in separate terminal windows:
   php artisan serve
   npm run dev
   ```

9. **Access Application**:
   - Storefront: `http://localhost:8000`
   - Admin Panel: `http://localhost:8000/admin`
   - Supplier Portal: `http://localhost:8000/supplier`
   - Reseller Portal: `http://localhost:8000/reseller`
   - Moderator Portal: `http://localhost:8000/moderator`

---

## 📂 Directory Structure

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # RESTful API Endpoints (Sanctum)
│   │   │   ├── Moderator/    # Moderator Shift & Task Controllers
│   │   │   ├── Reseller/     # Reseller Network & Cart Controllers
│   │   │   ├── Shop/         # Public Storefront & Customer Controllers
│   │   │   ├── Supplier/     # Multi-Vendor Portal Controllers
│   │   │   ├── Warehouse/    # Central Warehouse & Inventory ERP Controllers
│   │   │   └── Web/          # Super Admin, POS, Sales, Finance & System Controllers
│   │   └── Middleware/       # Role, Branch Access & Guard Middlewares
│   └── Models/               # Eloquent Models (40+ Models)
├── database/
│   ├── migrations/           # Database Schema Migrations
│   └── seeders/              # Initial Roles, Permissions & Master Data
├── public/
│   └── uploads/              # Product images, Banners, and File Assets
├── resources/
│   ├── css/                  # Styling & Stylesheets
│   ├── js/                   # Frontend Scripts
│   └── views/                # Modular Blade Templates (admin, warehouse, shop, etc.)
├── routes/
│   ├── api.php               # Mobile & External API Routes
│   ├── console.php           # Artisan Commands
│   └── web.php               # Multi-Portal Web Routes
└── scratch/                  # EPBX API Collections & Testing Tools
```

---

## 📄 License

This software is open-sourced under the [MIT License](LICENSE).
