# 🧾 ProcessMaker Vendor Batch Payments (Local PHP Simulation)

## 📘 Overview
This project simulates the **vendor batch payment workflow** of a ProcessMaker process locally in PHP.  
It replicates the logic of a **batch approval and execution process** where:

1. A batch payment request is created and approved.
2. The approval triggers the creation of child payment executions for each vendor.
3. Each payment execution can be simulated individually.
4. The final status of each payment is updated and reflected in the batch summary.

This allows you to validate workflow logic and data relationships before deploying to ProcessMaker.

---

## 🏗 Folder Structure
```
processmaker_batch_payments/
│
├── triggers/
│ ├── trigger_batchApprovalComplete.php # Simulates batch approval trigger
│ ├── trigger_createChildPaymentCase.php # Creates child payment cases
│ ├── trigger_updatePaymentStatus.php # Updates payment statuses after execution
│
├── tests/
│ ├── setup_database.php # Creates all database tables
│ ├── seed_sample_data.php # Inserts test vendors and batch data
│ ├── simulate_batch_approval.php # Simulates batch approval process
│ ├── simulate_payment_execution.php # Simulates payment execution for vendors
│ ├── simulate_payment_status_update.php # Simulates settlement and reconciliation
│
├── sql/
│ ├── create_tables.sql # SQL DDL for all project tables
│ ├── seed_sample_data.sql # Optional initial data for quick setup
│
├── utils/
│ ├── pm_db.php # Database connection helper (PostgreSQL)
│ ├── pm_logger.php # Simple file-based logger
│ ├── pm_case_context.php # Mimics ProcessMaker case context
│
├── logs/ # Auto-created on first run
│ └── trigger_test.log # Log output from triggers
│
└── readme.md
```

---

## ⚙️ Prerequisites

| Requirement | Description |
|--------------|-------------|
| **PHP ≥ 8.0** | With `pdo_pgsql` extension enabled |
| **PostgreSQL** | Local PostgreSQL database instance |
| **VS Code / Git Bash** | Recommended development setup |

---

## 🧩 Database Configuration

Update your database credentials in:


Example:
```php
$host = '127.0.0.1';
$port = '5432';
$db   = 'vendor_batch_payments';
$user = 'username';
$pass = 'userpassword';
```


## 🧱 Database Setup

### 1. Create the Database

```bash
createdb -U postgres vendor_batch_payments
```

### 2. Create the all required tables

```bash
php tests/setup_database.php
```

### 3. Insert Test Data

```bash
php tests/seed_sample_data.php
```

### 4. Verify Database Tables Creation

```bash
psql -U postgres -d vendor_batch_payments -c "\dt"
```

### 5. Confirm Inserted Data

```bash
psql -U postgres -d vendor_batch_payments -c "SELECT * FROM vendor_batch_items LIMIT 5;"
```


## 🔁 Workflow Simulation

You can run the workflow step by step:

| Step | Script                                         | Description                                      |
| ---- | ---------------------------------------------- | ------------------------------------------------ |
| 1️⃣  | `php tests/seed_sample_data.php`               | Seeds vendor and batch data                      |
| 2️⃣  | `php tests/simulate_batch_approval.php`        | Simulates the batch approval trigger             |
| 3️⃣  | `php tests/simulate_payment_execution.php`     | Simulates creation of payment executions         |
| 4️⃣  | `php tests/simulate_payment_status_update.php` | Randomly marks payments as `SUCCESS` or `FAILED` |

## 📊 Database Tables Overview

| Table                              | Description                                      |
| ---------------------------------- | ------------------------------------------------ |
| **vendor_payments**                | Represents parent batch-level payment requests   |
| **vendor_batch_items**             | Contains details for each vendor in a batch      |
| **payment_executions**             | Tracks child payment cases linked to each vendor |
| *(Optional)* **payment_audit_log** | Keeps a history of payment status updates        |


## 🪵 Logs

All triggered executions are logged in:
```bash
logs/trigger_test.log
```

## 🧠 Notes

The local setup mimics ProcessMaker triggers and data objects, allowing safe testing before deployment.

Each simulation step corresponds to a real ProcessMaker event or trigger.

You can integrate mock APIs or expand the scripts to test external payment gateways.

Author: Oluwaseun Aganran
Version: 1.0.0
Date: October 2025
