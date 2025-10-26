-- 🧹 Reset tables for clean test runs
TRUNCATE TABLE payment_executions RESTART IDENTITY CASCADE;
TRUNCATE TABLE vendor_batch_items RESTART IDENTITY CASCADE;
TRUNCATE TABLE vendor_payments RESTART IDENTITY CASCADE;

-------------------------------------------------------------
-- 🧾 Batch 1: CASE-001 (Approved, partially executed)
-------------------------------------------------------------
INSERT INTO vendor_payments (total_amount, batch_case_uid, initiator_user_uid, approval_status, payment_status, approved_at)
VALUES
(1250000.00, 'CASE-001', 'USR-INITIATOR-001', 'APPROVED', 'PARTIAL', now());

-- Vendors under Batch 1
INSERT INTO vendor_batch_items (batch_id, vendor_id, vendor_name, vendor_account_no, vendor_bank, currency, amount, payment_status)
VALUES
(1, 'VEND001', 'TechEdge Solutions Ltd', '0123456789', 'Access Bank', 'NGN', 450000.00, 'COMPLETED'),
(1, 'VEND002', 'BrightSpark Consulting', '0987654321', 'GTBank', 'NGN', 300000.00, 'PENDING'),
(1, 'VEND003', 'AlphaSupplies Nigeria Ltd', '2233445566', 'UBA', 'NGN', 250000.00, 'PENDING'),
(1, 'VEND004', 'NextGen Logistics Ltd', '4455667788', 'Zenith Bank', 'NGN', 250000.00, 'PENDING');

-- Payment executions under Batch 1
INSERT INTO payment_executions (batch_id, vendor_batch_item_id, ops_maker_uid, finance_checker_uid, payment_date, payment_status)
VALUES
(1, 1, 'USR-OPS-MAKER-001', 'USR-FIN-CHECKER-001', now(), 'COMPLETED');

-------------------------------------------------------------
-- 🧾 Batch 2: CASE-002 (Pending approval)
-------------------------------------------------------------
INSERT INTO vendor_payments (total_amount, batch_case_uid, initiator_user_uid, approval_status, payment_status, approved_at)
VALUES
(830000.00, 'CASE-002', 'USR-INITIATOR-002', 'PENDING', 'PENDING', NULL);

-- Vendors under Batch 2
INSERT INTO vendor_batch_items (batch_id, vendor_id, vendor_name, vendor_account_no, vendor_bank, currency, amount, payment_status)
VALUES
(2, 'VEND005', 'GreenField Engineering Ltd', '5566778899', 'First Bank', 'NGN', 200000.00, 'PENDING'),
(2, 'VEND006', 'BlueSky Advertising', '6677889900', 'Keystone Bank', 'NGN', 180000.00, 'PENDING'),
(2, 'VEND007', 'DataCore Systems Ltd', '7788990011', 'Stanbic IBTC', 'NGN', 150000.00, 'PENDING'),
(2, 'VEND008', 'PrimeHub Logistics', '8899001122', 'Fidelity Bank', 'NGN', 300000.00, 'PENDING');

-------------------------------------------------------------
-- ✅ Batch 3: CASE-003 (Fully executed, for reference)
-------------------------------------------------------------
INSERT INTO vendor_payments (total_amount, batch_case_uid, initiator_user_uid, approval_status, payment_status, approved_at)
VALUES
(600000.00, 'CASE-003', 'USR-INITIATOR-003', 'APPROVED', 'COMPLETED', now());

-- Vendors under Batch 3
INSERT INTO vendor_batch_items (batch_id, vendor_id, vendor_name, vendor_account_no, vendor_bank, currency, amount, payment_status)
VALUES
(3, 'VEND009', 'CloudNova Tech Ltd', '9900112233', 'Sterling Bank', 'NGN', 350000.00, 'COMPLETED'),
(3, 'VEND010', 'Apex Media Ltd', '1100223344', 'Wema Bank', 'NGN', 250000.00, 'COMPLETED');

-- Payment executions under Batch 3
INSERT INTO payment_executions (batch_id, vendor_batch_item_id, ops_maker_uid, finance_checker_uid, payment_date, payment_status)
VALUES
(3, 9, 'USR-OPS-MAKER-002', 'USR-FIN-CHECKER-002', now(), 'COMPLETED'),
(3, 10, 'USR-OPS-MAKER-002', 'USR-FIN-CHECKER-002', now(), 'COMPLETED');
