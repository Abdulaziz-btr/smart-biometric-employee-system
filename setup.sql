-- ============================================================
--  SMART EMPLOYEE MANAGEMENT SYSTEM — AVATA TRADING LTD
--  DATABASE SETUP  |  Run once in phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_employee
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_employee;

CREATE TABLE IF NOT EXISTS admin (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  username  VARCHAR(191) NOT NULL UNIQUE,
  password  VARCHAR(191) NOT NULL,
  createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS employee (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  fingerprintId     INT UNIQUE,
  name              VARCHAR(191) NOT NULL,
  position          VARCHAR(191),
  salaryRatePerDay  DOUBLE DEFAULT 10,
  dailySalesTarget  DOUBLE DEFAULT 500,
  email             VARCHAR(191) UNIQUE,
  loginOtp          VARCHAR(10),
  loginOtpExpiresAt DATETIME,
  createdAt         DATETIME DEFAULT CURRENT_TIMESTAMP,
  updatedAt         DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS attendancelog (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  employeeId    INT NOT NULL,
  fingerprintId INT,
  timestamp     DATETIME DEFAULT CURRENT_TIMESTAMP,
  status        VARCHAR(50) DEFAULT 'present',
  FOREIGN KEY (employeeId) REFERENCES employee(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS absencerequest (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  employeeId    INT NOT NULL,
  date          DATE NOT NULL,
  reason        VARCHAR(500),
  proofDocument VARCHAR(191),
  statusId      VARCHAR(50) DEFAULT 'pending',
  createdAt     DATETIME DEFAULT CURRENT_TIMESTAMP,
  updatedAt     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employeeId) REFERENCES employee(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS dailyreport (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  employeeId          INT NOT NULL,
  date                DATE NOT NULL,
  totalSalesAmount    DOUBLE DEFAULT 0,
  dailyTarget         DOUBLE DEFAULT 0,
  itemsAddedCount     INT DEFAULT 0,
  justificationFile   VARCHAR(191),
  justificationStatus VARCHAR(50) DEFAULT 'pending',
  justificationNote   TEXT,
  submittedAt         DATETIME DEFAULT CURRENT_TIMESTAMP,
  createdAt           DATETIME DEFAULT CURRENT_TIMESTAMP,
  updatedAt           DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employeeId) REFERENCES employee(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inventoryitem (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(191),
  quantityD INT DEFAULT 0,
  priceD    DOUBLE DEFAULT 0,
  addedById INT,
  createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS overtimerecord (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  employeeId INT NOT NULL,
  hours      DOUBLE DEFAULT 0,
  amount     DOUBLE DEFAULT 0,
  reason     VARCHAR(191),
  month      INT,
  year       INT,
  statusId   VARCHAR(50) DEFAULT 'pending',
  createdAt  DATETIME DEFAULT CURRENT_TIMESTAMP,
  updatedAt  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employeeId) REFERENCES employee(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS salaryrecord (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  employeeId  INT NOT NULL,
  monthYear   VARCHAR(10),
  totalSalary DOUBLE DEFAULT 0,
  deductions  DOUBLE DEFAULT 0,
  netSalary   DOUBLE DEFAULT 0,
  overtimePay DOUBLE DEFAULT 0,
  daysPresent INT DEFAULT 0,
  createdAt   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employeeId) REFERENCES employee(id) ON DELETE CASCADE
);

-- ── DEFAULT DATA ──────────────────────────────────────────
-- Admin password: admin123
INSERT INTO admin (username,password) VALUES
('admin','$2y$10$TKh8H1.PfburZ2Cx.CYLMe/AO5oCakCNIRSp7bF.oJc5I5oEFIFYa');

-- Employees (exact names from Figure 5.6)
INSERT INTO employee (fingerprintId,name,position,salaryRatePerDay,dailySalesTarget,email) VALUES
(1,'Mahoro Egide',    'Seller',          10.00,500.00,'mahoroegide77@gmail.com'),
(2,'Mugisha Light',   'Delivery Person', 10.00,500.00,'mugishalight@gmail.com'),
(3,'Mugisha Justin',  'Seller',          10.00,500.00,'mugishajustin@gmail.com'),
(4,'NKUSENGA Justin', 'Store Keeper',    10.00,500.00,'nkusengajustin@gmail.com');

-- Inventory items
INSERT INTO inventoryitem (name,quantityD,priceD) VALUES
('Rice 50kg bag',       100, 10.00),
('Cooking Oil 5L',      200, 22.00),
('Sugar 2kg',           300, 10.00),
('Maize Flour 25kg',    150, 20.00),
('Beans 10kg',           80, 15.00),
('Soap Bar (x10)',      400,  8.00),
('Mineral Water 1.5L',  500,  5.00),
('Biscuits Pack',       300, 12.00);
