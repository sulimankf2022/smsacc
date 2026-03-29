-- schema.sql for Multi-Tenant SMS Financial Management System

-- Table for Tenants
CREATE TABLE tenants (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Providers
CREATE TABLE providers (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    name VARCHAR(255) NOT NULL,
    contact_info VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Clients
CREATE TABLE clients (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    name VARCHAR(255) NOT NULL,
    contact_info VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Transactions
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    client_id INT REFERENCES clients(id),
    amount DECIMAL(10, 2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Expenses
CREATE TABLE expenses (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    amount DECIMAL(10, 2) NOT NULL,
    expense_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Employees
CREATE TABLE employees (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    salary DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Payroll
CREATE TABLE payroll (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id),
    employee_id INT REFERENCES employees(id),
    pay_period_start TIMESTAMP NOT NULL,
    pay_period_end TIMESTAMP NOT NULL,
    total_pay DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);