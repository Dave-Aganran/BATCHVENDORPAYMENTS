CREATE TABLE IF NOT EXISTS vendor_payments (
    id SERIAL PRIMARY KEY,
    total_amount NUMERIC(15,2),
    batch_case_uid VARCHAR(100),
    initiator_user_uid VARCHAR(100),
    approval_status VARCHAR(20) DEFAULT 'PENDING',
    payment_status VARCHAR(20) DEFAULT 'PENDING',
    approved_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

CREATE TABLE IF NOT EXISTS payment_executions (
    id SERIAL PRIMARY KEY,
    batch_id INT REFERENCES vendor_payments(id) ON DELETE CASCADE,
    vendor_batch_item_id INT,
    ops_maker_uid VARCHAR(100),
    ops_checker_uid VARCHAR(100),
    finance_maker_uid VARCHAR(100),
    finance_checker_uid VARCHAR(100),
    internal_control_uid VARCHAR(100),
    cfo_uid VARCHAR(100),
    payment_date TIMESTAMP,
    payment_status VARCHAR(20) DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now(),
    last_updatd_by VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS vendor_batch_items (
    id SERIAL PRIMARY KEY,
    batch_id INT REFERENCES vendor_payments(id) ON DELETE CASCADE,
    vendor_id VARCHAR(50),
    vendor_name VARCHAR(100),
    vendor_account_no VARCHAR(50),
    vendor_bank VARCHAR(100),
    currency VARCHAR(50),
    amount NUMERIC(20,2),
    payment_case_id INT REFERENCES payment_executions(id) ON DELETE CASCADE,
    payment_status VARCHAR(20) DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now(),
    last_updatd_by VARCHAR(50)
);
