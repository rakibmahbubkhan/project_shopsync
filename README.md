# Project_ShopSync

Modern Inventory Management System

## Stack

- Laravel 12 (API)
- Vue 3 (SPA)
- Tailwind CSS
- MySQL

## Installation

### Backend

cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

### Frontend

cd frontend
npm install
npm run dev



Core Modules

Dashboard

Sales

POS

Returns

Products

Categories

Warehouses

Inventory

Transfers

Customers

Expenses

Reports

Settings

----------------------------------------------------------------------------------------------------------------------
ShopSync ERP
Agricultural Machinery Workshop & Inventory Management System

ShopSync is a high-integrity ERP system specifically tailored for agricultural machinery workshops. It integrates real-time inventory tracking with automated double-entry accounting, ensuring that every physical part movement is accurately reflected in the financial books.

🚀 Key Modules & Features
1. User Management & RBAC
Secure Access: Multi-user environment with session management.

Role-Based Access Control (RBAC): Restrict sensitive financial and administrative pages based on user roles (Admin, Manager, Technician).

2. Inventory & Product Management
Machinery Tracking: Comprehensive database for parts and equipment.

Automated SKUs: Systematic generation of unique Stock Keeping Units for every new item.

Low Stock Alerts: Real-time monitoring of inventory levels with threshold notifications.

3. Sales & POS System
Real-time Transactions: Streamlined interface for processing workshop sales.

Profit Analytics: Automated calculation of Cost of Goods Sold (COGS) and Gross Profit per transaction.

Invoicing: Professional PDF receipt generation for customers.

4. Purchases & Supplier Management
Restocking Workflow: Log incoming machinery parts from vendors.

Average Costing: Automatic updating of product cost based on new purchase prices.

Vendor Database: Centralized management of equipment suppliers.

5. Stock Operations
Internal Transfers: Move machinery parts between multiple workshop warehouses.

Manual Adjustments: Correct stock levels discovered during physical audits.

Dual-Log Tracking: Detailed history of all inventory movements.

6. Accounting & Finance
Automated Ledger: Operational steps (Sales, Purchases) automatically post balanced journal entries.

Double-Entry Integrity: Systematic validation ensures Total Debits always equal Total Credits.

Trial Balance: Real-time financial health summaries including Assets, Liabilities, and Revenue.

7. Returns & Refunds
Workflow Reversals: Systematic handling of faulty parts or order cancellations.

Stock Restoration: Automatically returns items to inventory while reversing financial gains.

8. Reporting & System Auditing
KPI Dashboard: Visual trends for revenue, profit, and stock health.

Audit Trails: Complete "paper trail" showing who changed what and when for maximum accountability.

🛠 Installation Guide
Prerequisites
PHP: >= 8.2

Node.js: >= 18.x

Composer

MySQL or similar SQL database

Backend Setup (Laravel)
Navigate to the backend directory:

Install dependencies:

Configure environment:

Database Migration & Seeding:
Ensure your database credentials are set in .env.

Start the API server:

Frontend Setup (Vue.js)
Navigate to the frontend directory:

Install packages:

Set API URL:
Create a .env file and set VITE_API_URL=http://localhost:8000/api.

Launch the development server:

🔐 Default Credentials
Once the database is seeded, you can log in with:

Email: admin@shopsync.com (or as configured in DatabaseSeeder)

Password: password