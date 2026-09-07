# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Ultimate POS (HSF Store) — a multi-tenant Point of Sale and Inventory Management system built on **Laravel 5.8** with **PHP ^7.1.3** and **MySQL**. Features include sales/purchase management, multi-location inventory, financial accounting, restaurant module, and 25+ report types.

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Build frontend assets
npm run dev            # development build
npm run watch          # watch mode
npm run production     # production build

# Database
php artisan migrate
php artisan db:seed

# Run tests
php artisan test
vendor/bin/phpunit                    # all tests
vendor/bin/phpunit tests/Unit         # unit tests only
vendor/bin/phpunit --filter=TestName  # single test

# Clear caches (useful after config/route changes)
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Custom artisan commands
php artisan pos:createDummyBusiness   # generate demo data
php artisan pos:DBBackup              # database backup
php artisan pos:mapPurchaseSell       # link purchases to sales
php artisan pos:recurringExpense      # process recurring expenses
php artisan pos:recurringInvoice      # generate recurring invoices
php artisan pos:updateRewardPoints    # calculate reward points
```

## Architecture

### Business Logic Pattern

Controllers delegate to **Utility classes** in `app/Utils/` — these are the core of the business logic layer:

- **TransactionUtil** (~197KB): All transaction operations (sales, purchases, payments, stock adjustments). The most complex file in the codebase.
- **ProductUtil** (~67KB): Product CRUD, variations, pricing, stock management.
- **BusinessUtil**: Business settings and configurations.
- **CashRegisterUtil**: POS cash register operations.
- **ContactUtil**: Customer/supplier/lead operations.
- **NotificationUtil**: Notification templating.
- **ModuleUtil**: Module management.

Controllers inject these Utils via constructor. When modifying transaction or product logic, start in the relevant Util class, not the controller.

### Multi-Tenancy (Session-Based)

Every authenticated request passes through middleware that loads business context into the session:

1. `SetSessionData` middleware loads user's `business_id` and `location_id` into the session
2. Nearly all database queries are scoped by `business_id`
3. Users can have location-specific permissions (`location.{location_id}`)

User roles follow the naming pattern `role_name#business_id` (Spatie Permission package).

### Dual Authentication System

- **User model** (`app/User.php`): Staff/admin authentication
- **Contact model** (`app/Contact.php`): Also extends `Authenticatable` — used for customer-facing auth
- Both share the same auth framework but serve different roles

### Key Controllers (by complexity)

- **ReportController** (~166KB): All financial and business reports
- **SellPosController** (~98KB): POS interface and sale transactions
- **ProductController** (~93KB): Product management
- **ContactController** (~73KB): Customer/supplier management

### Frontend

- **Laravel Mix** (Webpack) compiles assets
- **AdminLTE 2** dashboard template with Bootstrap
- **jQuery 3.6** is the primary JS framework (Vue 2.6 available but mostly server-side rendering)
- **DataTables** (Yajra) for all tabular data with server-side processing
- Compiled bundles: `public/js/vendor.js` and `public/css/vendor.css`
- RTL CSS bundle available for Arabic/RTL languages

### Middleware Stack (authenticated routes)

`setData → auth → SetSessionData → language → timezone → AdminSidebarMenu → CheckUserLogin`

`AdminSidebarMenu` (~41KB) dynamically builds the navigation based on user permissions.

### Transaction Model

The `Transaction` model is polymorphic — the `type` field determines behavior:
- `sell`, `purchase`, `sell_return`, `purchase_return`, `opening_stock`, `expense`, `stock_adjustment`

All transaction types share the same table and model but follow different logic paths in `TransactionUtil`.

### API Response Pattern

The base controller provides standardized JSON responses:
- `respondSuccess($message, $additional_data)`
- `respondWithError($message)`
- `respondWentWrong($exception)`

### Soft Deletes

Many models use `SoftDeletes` trait for data retention. When querying deleted records, use `withTrashed()`.

## Environment Setup

Key `.env` variables beyond standard Laravel:
- Payment gateways: Stripe, PayPal, Razorpay, PesaPal credentials
- `PUSHER_*`: Real-time broadcasting (used for kitchen orders, notifications)
- `GOOGLE_MAP_API_KEY`: Location features
- Backup: Local or Dropbox (`DROPBOX_ACCESS_TOKEN`)

## Database

80+ migrations. Core tables: `transactions`, `transaction_sell_lines`, `transaction_payments`, `products`, `variations`, `contacts`, `business`, `business_locations`, `users`.

The `helpers.php` file (`app/Http/helpers.php`) contains global utility functions loaded via Composer autoload.
