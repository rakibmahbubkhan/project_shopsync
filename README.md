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


1. User Management & Access Control
This module handles authentication, user profiles, and the Role-Based Access Control (RBAC) system to secure the agricultural machinery workshop's data.

Key Files: UserController, AuthController, User, Role, and Permission.

Logic: Managed via RoleMiddleware and CheckPermission middleware.

2. Inventory & Product Management
Manages the core database of machinery, parts, and their attributes.

Products: Tracking individual items, their SKUs, and prices.

Classification: Organizing items via Category, Brand, and Unit (e.g., pieces, sets).

Warehouses: Managing multiple storage locations for machinery parts.

3. Sales & Point of Sale (POS)
The transaction engine for recording sales and generating receipts.

POS System: A real-time interface for processing sales.

Sales Management: Tracking transaction history through SaleController and Sale models.

Customer Management: Maintaining a database of clients purchasing machinery.

4. Purchases & Supplier Management
Handles the acquisition of raw materials and parts from external vendors.

Purchases: Recording incoming stock deliveries and costs.

Suppliers: Managing vendor contact information and performance.

5. Stock Operations
Detailed tracking of physical inventory movements across the workshop.

Movement: Tracking stock changes via StockService and StockLog.

Adjustments & Transfers: Handling manual stock corrections and moving parts between warehouses.

Auditing: Recording every change in the InventoryLedger.

6. Accounting & Finance
The general ledger system that integrates business operations with financial records.

General Ledger: Managed by AccountingService to create balanced JournalEntry records.

Chart of Accounts: Defining the financial structure (Assets, Liabilities, Revenue, Expenses).

7. Returns & Refunds
Workflow for handling errors or cancellations in the sales process.

Returns: Documenting returned machinery parts through SaleReturn.

Financials: Processing monetary Refund records.

8. Reporting & Analytics
Provides business intelligence and financial summaries for workshop management.

Dashboard: Real-time overview of revenue, sales, and low-stock alerts.

Financial Reports: Generating Trial Balances and Profit & Loss statements.

9. System Auditing
Maintains a tamper-evident log of all significant administrative actions within the ERP.

Key Files: AuditLogController and AuditLog model.