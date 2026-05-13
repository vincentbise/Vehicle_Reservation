-- ═══════════════════════════════════════════════════════════════════
--  USeP Vehicle Reservation System — Full Database Schema + Seed Data
--  Engine: MySQL 8.0+  |  Charset: utf8mb4_unicode_ci
-- ═══════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS usep_vrs
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE usep_vrs;

-- ── 1. Users ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    user_id       INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    employee_id   VARCHAR(20)      NOT NULL,
    full_name     VARCHAR(100)     NOT NULL,
    email         VARCHAR(100)     NOT NULL,
    username      VARCHAR(50)      NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    role          ENUM(
                    'admin',
                    'requester',
                    'unit_head',
                    'asd_coordinator',
                    'driver'
                  ) NOT NULL DEFAULT 'requester',
    department    VARCHAR(100)     NULL,
    contact_no    VARCHAR(20)      NULL,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),
    UNIQUE KEY uq_employee_id (employee_id),
    UNIQUE KEY uq_email       (email),
    UNIQUE KEY uq_username    (username),
    INDEX idx_role            (role),
    INDEX idx_department      (department)
) ENGINE=InnoDB;

-- ── 2. Vehicles ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    plate_number  VARCHAR(15)      NOT NULL,
    make_model    VARCHAR(100)     NOT NULL,
    vehicle_type  VARCHAR(50)      NULL,
    capacity      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    year          YEAR             NULL,
    color         VARCHAR(40)      NULL,
    status        ENUM(
                    'available',
                    'in_use',
                    'maintenance',
                    'retired'
                  ) NOT NULL DEFAULT 'available',
    notes         TEXT             NULL,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (vehicle_id),
    UNIQUE KEY uq_plate   (plate_number),
    INDEX idx_status      (status)
) ENGINE=InnoDB;

-- ── 3. Reservations ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reference_no     VARCHAR(20)   NOT NULL,
    requester_id     INT UNSIGNED  NOT NULL,
    vehicle_id       INT UNSIGNED  NULL,
    purpose          TEXT          NOT NULL,
    destination      VARCHAR(255)  NOT NULL,
    passengers       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    departure_date   DATE          NOT NULL,
    departure_time   TIME          NOT NULL,
    return_date      DATE          NOT NULL,
    return_time      TIME          NOT NULL,
    status           ENUM(
                       'pending',
                       'unit_approved',
                       'asd_approved',
                       'dispatched',
                       'completed',
                       'rejected',
                       'cancelled'
                     ) NOT NULL DEFAULT 'pending',
    remarks          TEXT          NULL,
    requested_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (reservation_id),
    UNIQUE KEY uq_reference_no (reference_no),
    INDEX idx_status           (status),
    INDEX idx_requester        (requester_id),
    INDEX idx_departure_date   (departure_date),

    CONSTRAINT fk_res_requester FOREIGN KEY (requester_id)
        REFERENCES users (user_id) ON DELETE CASCADE,

    CONSTRAINT fk_res_vehicle   FOREIGN KEY (vehicle_id)
        REFERENCES vehicles (vehicle_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 4. Approvals ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS approvals (
    approval_id      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reservation_id   INT UNSIGNED  NOT NULL,
    approved_by      INT UNSIGNED  NOT NULL,
    approval_level   ENUM('unit_head', 'asd_coordinator') NOT NULL,
    decision         ENUM('approved', 'rejected')         NOT NULL,
    remarks          TEXT          NULL,
    decided_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (approval_id),
    INDEX idx_reservation (reservation_id),
    INDEX idx_approver    (approved_by),

    CONSTRAINT fk_appr_reservation FOREIGN KEY (reservation_id)
        REFERENCES reservations (reservation_id) ON DELETE CASCADE,

    CONSTRAINT fk_appr_user         FOREIGN KEY (approved_by)
        REFERENCES users        (user_id)        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── 5. Drivers ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS drivers (
    driver_id      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED  NOT NULL,
    license_no     VARCHAR(30)   NOT NULL,
    license_expiry DATE          NOT NULL,
    is_available   TINYINT(1)    NOT NULL DEFAULT 1,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (driver_id),
    UNIQUE KEY uq_user_id    (user_id),
    UNIQUE KEY uq_license_no (license_no),

    CONSTRAINT fk_drv_user FOREIGN KEY (user_id)
        REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 6. Dispatch Logs ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS dispatch_logs (
    log_id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reservation_id    INT UNSIGNED  NOT NULL,
    driver_id         INT UNSIGNED  NOT NULL,
    vehicle_id        INT UNSIGNED  NOT NULL,
    start_mileage     DECIMAL(10,2) NULL     DEFAULT 0.00,
    end_mileage       DECIMAL(10,2) NULL,
    fuel_consumed     DECIMAL(8,2)  NULL,
    actual_departure  DATETIME      NULL,
    actual_return     DATETIME      NULL,
    trip_notes        TEXT          NULL,
    logged_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (log_id),
    UNIQUE KEY uq_dispatch_res (reservation_id),
    INDEX idx_driver           (driver_id),
    INDEX idx_vehicle          (vehicle_id),

    CONSTRAINT fk_log_reservation FOREIGN KEY (reservation_id)
        REFERENCES reservations (reservation_id) ON DELETE CASCADE,

    CONSTRAINT fk_log_driver      FOREIGN KEY (driver_id)
        REFERENCES drivers      (driver_id)      ON DELETE RESTRICT,

    CONSTRAINT fk_log_vehicle     FOREIGN KEY (vehicle_id)
        REFERENCES vehicles     (vehicle_id)     ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ═══════════════════════════════════════════════════════════════════
--  SEED DATA — Default accounts and sample fleet
-- ═══════════════════════════════════════════════════════════════════

-- Passwords are bcrypt hashes — regenerate with: php -r "echo password_hash('pw', PASSWORD_BCRYPT);"

INSERT INTO users
    (employee_id, full_name, email, username, password_hash, role, department, contact_no)
VALUES
-- Administrator  (password: admin@USeP2026)
('EMP-0001', 'System Administrator', 'admin@usep.edu.ph',
 'admin',
 '$2y$10$1HUxPEzHlzg/vFU3xSfqAOjZdA.irdReUT8fHcMdr8W.8AbhJOj9u',
 'admin', 'Administrative Services Division', '082-227-8192'),

-- ASD Coordinator  (password: password123)
('EMP-0002', 'Maria Clara Reyes', 'mclara.reyes@usep.edu.ph',
 'asdcoord',
 '$2y$10$YarHdN2Q5BHpsXhTK2ae/.mceB.hPAV3Q8k9eEZ7hkjvIQ6jIrsZW',
 'asd_coordinator', 'Administrative Services Division', '09171234567'),

-- Unit Head  (password: password123)
('EMP-0003', 'Jose Santos Jr.', 'jose.santos@usep.edu.ph',
 'unithead',
 '$2y$10$YarHdN2Q5BHpsXhTK2ae/.mceB.hPAV3Q8k9eEZ7hkjvIQ6jIrsZW',
 'unit_head', 'College of Engineering and Technology', '09281234567'),

-- Requester  (password: password123)
('EMP-0004', 'Ana Marie Gonzales', 'ana.gonzales@usep.edu.ph',
 'msantos',
 '$2y$10$YarHdN2Q5BHpsXhTK2ae/.mceB.hPAV3Q8k9eEZ7hkjvIQ6jIrsZW',
 'requester', 'Registrar Office', '09391234567'),

-- Driver  (password: password123)
('EMP-0005', 'Juan Dela Cruz', 'juan.delacruz@usep.edu.ph',
 'jreyes',
 '$2y$10$YarHdN2Q5BHpsXhTK2ae/.mceB.hPAV3Q8k9eEZ7hkjvIQ6jIrsZW',
 'driver', 'Motorpool', '09501234567');


-- Sample vehicle fleet
INSERT INTO vehicles (plate_number, make_model, vehicle_type, capacity, year, color, status) VALUES
('USeP-0001', 'Toyota HiAce Grandia',   'Van',     15, 2022, 'Silver',  'available'),
('USeP-0002', 'Toyota Coaster',          'Bus',     25, 2021, 'White',   'available'),
('USeP-0003', 'Mitsubishi L300 FB',      'Van',     14, 2020, 'White',   'available'),
('USeP-0004', 'Toyota Fortuner',         'SUV',      7, 2023, 'Black',   'available'),
('USeP-0005', 'Toyota Innova',           'Van',      7, 2022, 'Pearl',   'maintenance');


-- Driver profile record for EMP-0005
INSERT INTO drivers (user_id, license_no, license_expiry, is_available)
    SELECT user_id, 'N05-12-345678', '2028-06-30', 1
    FROM   users WHERE username = 'jreyes';
