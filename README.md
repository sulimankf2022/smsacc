# SMS Finance - Multi-Tenant Financial Management System

A complete web-based financial management system for SMS traffic/switch businesses, featuring multi-tenant isolation, profile-based ledgers, multi-currency support, and a clean dashboard.

## Features

- **Multi-Tenant Architecture** — Completely isolated workspaces per user/tenant
- **Profile-Based Ledgers** — Dedicated Provider and Client profiles with full transaction history and running balances
- **Multi-Currency Support** — USD, EUR, ILS with tenant-configurable exchange rates
- **Dashboard** — Key financial metrics: receivables, expenses, payroll, overdue items
- **Provider Management** — Receivables, payments, adjustments, running balance
- **Client Management** — Invoices, payments, adjustments, running balance
- **Expenses Tracking** — Multi-currency expense management
- **Employee Payroll** — Salaries, advances, payment history
- **Admin Panel** — Super Admin tenant/user management
- **Secure Authentication** — Session-based login, CSRF protection, input validation

## Tech Stack

- PHP 8.x (PDO)
- SQLite database
- Bootstrap 5.3 UI
- Vanilla JS

## Quick Start

### Requirements
- PHP 8.0+ with PDO SQLite extension
- Web server (Apache/Nginx) or PHP built-in server

### Setup

1. Clone the repository
2. Point your web server root to the `public/` directory
3. Run setup once to initialize the database and seed demo data:

```bash
php setup.php
```

4. Navigate to `http://your-server/` in your browser

### Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Super Admin | `admin` | `admin123` |
| Demo Tenant Owner | `demo_owner` | `demo123` |

> **Security Note:** Change all default passwords immediately after setup. Delete or secure `setup.php` after first run.

## File Structure

```
├── public/                  # Web root
│   ├── index.php            # Main router/entry point
│   ├── .htaccess            # URL rewriting
│   └── assets/              # CSS, JS
├── app/
│   ├── config.php           # Configuration constants
│   ├── database.php         # DB connection + schema initialization
│   ├── auth.php             # Authentication functions
│   ├── helpers.php          # CSRF, formatting, flash messages
│   ├── middleware.php       # Auth middleware
│   ├── router.php           # Routing logic
│   ├── controllers/         # Business logic (8 controllers)
│   └── views/               # HTML templates
│       ├── layout/          # Shared header, sidebar, footer
│       ├── auth/            # Login page
│       ├── dashboard/       # Dashboard
│       ├── providers/       # Provider list + profile
│       ├── clients/         # Client list + profile
│       ├── expenses/        # Expense management
│       ├── employees/       # Employee list + payroll profile
│       ├── settings/        # Currency rates, password change
│       └── admin/           # Super admin panel
├── database/
│   └── schema.sql           # Complete database schema
└── setup.php                # One-time setup + seed data
```

## Database Schema

All business tables include `tenant_id` for complete data isolation:

| Table | Purpose |
|-------|---------|
| `tenants` | Tenant/company accounts |
| `users` | User accounts with roles |
| `settings` | Per-tenant settings |
| `currency_rates` | Per-tenant exchange rates |
| `providers` | Provider/vendor profiles |
| `provider_transactions` | Provider ledger entries |
| `clients` | Client/customer profiles |
| `client_transactions` | Client ledger entries |
| `expenses` | Expense records |
| `employees` | Employee profiles |
| `payroll_records` | Salary/advance payment history |

## Roles

| Role | Access |
|------|--------|
| `super_admin` | Full system access, tenant/user management |
| `owner` | Full access to own tenant workspace |
| `staff` | Limited access within assigned tenant |

## Tenant Isolation

Every database query filters by `tenant_id = ?` ensuring complete data segregation. Users can only see and modify records belonging to their own tenant.

## Apache Configuration

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/smsacc/public
    <Directory /path/to/smsacc/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Nginx Configuration

```nginx
server {
    listen 80;
    root /path/to/smsacc/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```
