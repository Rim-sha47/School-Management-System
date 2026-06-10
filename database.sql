-- ============================================================
--  The Providence School Management System — Full Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS providence_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE providence_school;

-- ── Users (Authentication for all roles) ──────────────────────
CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    role         ENUM('super_admin','admin','teacher','student','parent') NOT NULL DEFAULT 'student',
    status       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login   DATETIME DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Academic Sessions ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS academic_sessions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50) NOT NULL,           -- e.g. "2025-2026"
    start_date DATE NOT NULL,
    end_date   DATE NOT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Classes ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS classes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(50)  NOT NULL,        -- e.g. "Class 6"
    numeric_name INT NOT NULL DEFAULT 1,
    capacity     INT NOT NULL DEFAULT 40,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Sections ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sections (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    class_id         INT NOT NULL,
    name             VARCHAR(10) NOT NULL,     -- "A", "B", "Pre-Med"
    class_teacher_id INT DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- ── Subjects ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS subjects (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    code       VARCHAR(20)  NOT NULL UNIQUE,
    class_id   INT DEFAULT NULL,
    type       ENUM('theory','practical','both') NOT NULL DEFAULT 'theory',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- ── Teachers ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS teachers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    teacher_id   VARCHAR(20) NOT NULL UNIQUE,
    first_name   VARCHAR(50) NOT NULL,
    last_name    VARCHAR(50) NOT NULL,
    email        VARCHAR(100) DEFAULT NULL,
    phone        VARCHAR(20)  DEFAULT NULL,
    gender       ENUM('male','female','other') DEFAULT NULL,
    dob          DATE DEFAULT NULL,
    address      TEXT DEFAULT NULL,
    department   VARCHAR(100) DEFAULT NULL,
    qualification VARCHAR(100) DEFAULT NULL,
    join_date    DATE DEFAULT NULL,
    photo        VARCHAR(255) DEFAULT NULL,
    status       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Teacher ↔ Subject (Many-to-Many) ─────────────────────────
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id   INT DEFAULT NULL,
    UNIQUE KEY uniq_ts (teacher_id, subject_id, class_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES classes(id)  ON DELETE SET NULL
);

-- ── Students ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS students (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT NOT NULL,
    registration_number VARCHAR(20) NOT NULL UNIQUE,
    first_name          VARCHAR(50) NOT NULL,
    last_name           VARCHAR(50) NOT NULL,
    email               VARCHAR(100) DEFAULT NULL,
    phone               VARCHAR(20)  DEFAULT NULL,
    gender              ENUM('male','female','other') DEFAULT NULL,
    dob                 DATE DEFAULT NULL,
    blood_group         VARCHAR(5)  DEFAULT NULL,
    address             TEXT DEFAULT NULL,
    class_id            INT DEFAULT NULL,
    section_id          INT DEFAULT NULL,
    session_id          INT DEFAULT NULL,
    roll_number         VARCHAR(20) DEFAULT NULL,
    photo               VARCHAR(255) DEFAULT NULL,
    status              ENUM('active','inactive','graduated','transferred') NOT NULL DEFAULT 'active',
    admission_date      DATE DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)             ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES classes(id)           ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id)          ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE SET NULL
);

-- ── Parents ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS parents (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT DEFAULT NULL,
    first_name   VARCHAR(50) NOT NULL,
    last_name    VARCHAR(50) NOT NULL,
    relation     ENUM('father','mother','guardian') NOT NULL DEFAULT 'father',
    email        VARCHAR(100) DEFAULT NULL,
    phone        VARCHAR(20)  DEFAULT NULL,
    cnic         VARCHAR(20)  DEFAULT NULL,
    occupation   VARCHAR(100) DEFAULT NULL,
    address      TEXT DEFAULT NULL,
    status       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ── Parent ↔ Student (Many-to-Many) ──────────────────────────
CREATE TABLE IF NOT EXISTS parent_students (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    parent_id  INT NOT NULL,
    student_id INT NOT NULL,
    UNIQUE KEY uniq_ps (parent_id, student_id),
    FOREIGN KEY (parent_id)  REFERENCES parents(id)  ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- ── Attendance ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS attendance (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id   INT DEFAULT NULL,
    section_id INT DEFAULT NULL,
    date       DATE NOT NULL,
    status     ENUM('present','absent','late','leave') NOT NULL DEFAULT 'present',
    remarks    VARCHAR(255) DEFAULT NULL,
    marked_by  INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_att (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES classes(id)  ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (marked_by)  REFERENCES users(id)    ON DELETE SET NULL
);

-- ── Exams ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS exams (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    type         ENUM('unit_test','mid_term','final','other') NOT NULL DEFAULT 'other',
    class_id     INT DEFAULT NULL,
    session_id   INT DEFAULT NULL,
    start_date   DATE DEFAULT NULL,
    end_date     DATE DEFAULT NULL,
    total_marks  INT NOT NULL DEFAULT 100,
    passing_marks INT NOT NULL DEFAULT 40,
    status       ENUM('upcoming','ongoing','completed') NOT NULL DEFAULT 'upcoming',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id)   REFERENCES classes(id)           ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE SET NULL
);

-- ── Exam Timetable ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS exam_timetable (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    exam_id    INT NOT NULL,
    subject_id INT NOT NULL,
    exam_date  DATE NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time   TIME DEFAULT NULL,
    venue      VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (exam_id)    REFERENCES exams(id)    ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- ── Results ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS results (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT NOT NULL,
    exam_id        INT NOT NULL,
    subject_id     INT NOT NULL,
    marks_obtained DECIMAL(6,2) NOT NULL DEFAULT 0,
    total_marks    INT NOT NULL DEFAULT 100,
    grade          VARCHAR(5)  DEFAULT NULL,
    remarks        VARCHAR(255) DEFAULT NULL,
    is_published   TINYINT(1)  NOT NULL DEFAULT 0,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_result (student_id, exam_id, subject_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id)    REFERENCES exams(id)    ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- ── Assignments ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS assignments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    description  TEXT DEFAULT NULL,
    subject_id   INT DEFAULT NULL,
    class_id     INT DEFAULT NULL,
    section_id   INT DEFAULT NULL,
    teacher_id   INT DEFAULT NULL,
    assigned_date DATE NOT NULL,
    due_date     DATE NOT NULL,
    total_marks  INT NOT NULL DEFAULT 10,
    status       ENUM('active','closed') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id)  REFERENCES subjects(id)  ON DELETE SET NULL,
    FOREIGN KEY (class_id)    REFERENCES classes(id)   ON DELETE SET NULL,
    FOREIGN KEY (section_id)  REFERENCES sections(id)  ON DELETE SET NULL,
    FOREIGN KEY (teacher_id)  REFERENCES teachers(id)  ON DELETE SET NULL
);

-- ── Assignment Submissions ────────────────────────────────────
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id   INT NOT NULL,
    student_id      INT NOT NULL,
    submission_date DATETIME DEFAULT NULL,
    marks_obtained  DECIMAL(5,2) DEFAULT NULL,
    status          ENUM('submitted','pending','graded','late') NOT NULL DEFAULT 'pending',
    remarks         VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY uniq_sub (assignment_id, student_id),
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)    REFERENCES students(id)    ON DELETE CASCADE
);

-- ── Fee Structures ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fee_structures (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    class_id     INT DEFAULT NULL,
    session_id   INT DEFAULT NULL,
    amount       DECIMAL(10,2) NOT NULL,
    type         ENUM('monthly','term','annual','one_time') NOT NULL DEFAULT 'monthly',
    due_day      INT NOT NULL DEFAULT 10,      -- day of month
    late_fee     DECIMAL(10,2) NOT NULL DEFAULT 0,
    description  TEXT DEFAULT NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id)   REFERENCES classes(id)           ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE SET NULL
);

-- ── Fee Payments ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fee_payments (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    student_id       INT NOT NULL,
    fee_structure_id INT DEFAULT NULL,
    challan_no       VARCHAR(30) NOT NULL UNIQUE,
    amount           DECIMAL(10,2) NOT NULL,
    late_fee         DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount         DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_paid       DECIMAL(10,2) NOT NULL,
    payment_date     DATE DEFAULT NULL,
    due_date         DATE DEFAULT NULL,
    payment_method   ENUM('cash','bank','online','cheque') NOT NULL DEFAULT 'cash',
    status           ENUM('paid','unpaid','partial','overdue') NOT NULL DEFAULT 'unpaid',
    month_year       VARCHAR(10) DEFAULT NULL,  -- "2026-06"
    remarks          TEXT DEFAULT NULL,
    collected_by     INT DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id)       REFERENCES students(id)       ON DELETE CASCADE,
    FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE SET NULL,
    FOREIGN KEY (collected_by)     REFERENCES users(id)          ON DELETE SET NULL
);

-- ── Library Books ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS books (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(200) NOT NULL,
    author         VARCHAR(150) DEFAULT NULL,
    isbn           VARCHAR(30)  DEFAULT NULL UNIQUE,
    publisher      VARCHAR(150) DEFAULT NULL,
    category       VARCHAR(100) DEFAULT NULL,
    edition        VARCHAR(50)  DEFAULT NULL,
    publish_year   YEAR DEFAULT NULL,
    total_copies   INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    shelf_location VARCHAR(50) DEFAULT NULL,
    description    TEXT DEFAULT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Book Issues ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS book_issues (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    book_id     INT NOT NULL,
    student_id  INT NOT NULL,
    issue_date  DATE NOT NULL,
    due_date    DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    fine        DECIMAL(8,2) NOT NULL DEFAULT 0,
    status      ENUM('issued','returned','overdue') NOT NULL DEFAULT 'issued',
    issued_by   INT DEFAULT NULL,
    remarks     VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id)    REFERENCES books(id)    ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by)  REFERENCES users(id)    ON DELETE SET NULL
);

-- ── Transport Vehicles ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS vehicles (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    registration_no VARCHAR(30)  NOT NULL UNIQUE,
    make_model      VARCHAR(100) DEFAULT NULL,
    capacity        INT NOT NULL DEFAULT 30,
    driver_name     VARCHAR(100) DEFAULT NULL,
    driver_phone    VARCHAR(20)  DEFAULT NULL,
    status          ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Transport Routes ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS routes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    vehicle_id  INT DEFAULT NULL,
    stops       TEXT DEFAULT NULL,
    fare        DECIMAL(8,2) NOT NULL DEFAULT 0,
    description TEXT DEFAULT NULL,
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

-- ── Student ↔ Route ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS student_transport (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    route_id   INT NOT NULL,
    pickup_stop VARCHAR(100) DEFAULT NULL,
    UNIQUE KEY uniq_st (student_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id)   REFERENCES routes(id)   ON DELETE CASCADE
);

-- ── Notice Board ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notices (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    content      TEXT NOT NULL,
    audience     SET('all','students','teachers','parents') NOT NULL DEFAULT 'all',
    priority     ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal',
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    publish_date DATE NOT NULL,
    expiry_date  DATE DEFAULT NULL,
    created_by   INT DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ── Events ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS events (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    description  TEXT DEFAULT NULL,
    event_date   DATE NOT NULL,
    start_time   TIME DEFAULT NULL,
    end_time     TIME DEFAULT NULL,
    venue        VARCHAR(150) DEFAULT NULL,
    type         ENUM('academic','sports','cultural','holiday','exam','other') NOT NULL DEFAULT 'other',
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_by   INT DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ── Timetable ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS timetable (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    class_id    INT NOT NULL,
    section_id  INT DEFAULT NULL,
    subject_id  INT DEFAULT NULL,
    teacher_id  INT DEFAULT NULL,
    day         ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
    period_no   INT NOT NULL DEFAULT 1,
    start_time  TIME NOT NULL,
    end_time    TIME NOT NULL,
    room        VARCHAR(50) DEFAULT NULL,
    session_id  INT DEFAULT NULL,
    FOREIGN KEY (class_id)   REFERENCES classes(id)           ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id)          ON DELETE SET NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)          ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)          ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE SET NULL
);

-- ── Certificates ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS certificates (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    type          ENUM('character','bonafide','transfer','merit') NOT NULL,
    issue_date    DATE NOT NULL,
    certificate_no VARCHAR(30) NOT NULL UNIQUE,
    issued_by     INT DEFAULT NULL,
    remarks       TEXT DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by)  REFERENCES users(id)    ON DELETE SET NULL
);

-- ── System Settings ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_val TEXT DEFAULT NULL,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
--  SEED DATA
-- ============================================================

-- Default passwords are 'password123'
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO users (username, password, role, status) VALUES
('admin',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active'),
('admin1',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',       'active'),
('TCH-1001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher',     'active'),
('TCH-1002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher',     'active'),
('STD-2001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',     'active'),
('STD-2002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',     'active'),
('STD-2003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',     'active'),
('PAR-3001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent',      'active');

-- Academic Session
INSERT INTO academic_sessions (name, start_date, end_date, is_current) VALUES
('2025-2026', '2025-04-01', '2026-03-31', 1),
('2024-2025', '2024-04-01', '2025-03-31', 0);

-- Classes
INSERT INTO classes (name, numeric_name, capacity) VALUES
('Class 1',  1,  40), ('Class 2',  2,  40), ('Class 3',  3,  40),
('Class 4',  4,  40), ('Class 5',  5,  40), ('Class 6',  6,  40),
('Class 7',  7,  40), ('Class 8',  8,  40), ('Class 9',  9,  40),
('Class 10', 10, 40), ('Class 11', 11, 40), ('Class 12', 12, 40);

-- Sections
INSERT INTO sections (class_id, name) VALUES
(6,  'A'), (6,  'B'),
(9,  'A'), (9,  'B'), (9,  'C'),
(10, 'A'), (10, 'B'),
(11, 'Pre-Med'), (11, 'Pre-Eng'),
(12, 'Pre-Med'), (12, 'Pre-Eng');

-- Subjects
INSERT INTO subjects (name, code, type) VALUES
('Mathematics',        'MATH',    'theory'),
('English',            'ENG',     'theory'),
('Physics',            'PHY',     'both'),
('Chemistry',          'CHEM',    'both'),
('Biology',            'BIO',     'both'),
('Computer Science',   'CS',      'both'),
('Urdu',               'URDU',    'theory'),
('Islamiyat',          'ISLM',    'theory'),
('Pakistan Studies',   'PST',     'theory'),
('General Science',    'GSCI',    'theory');

-- Teachers
INSERT INTO teachers (user_id, teacher_id, first_name, last_name, email, phone, gender, department, qualification, join_date, status) VALUES
(3, 'TCH-1001', 'Muhammad', 'Tariq',  'tariq@providence.edu.pk', '0300-1234567', 'male',   'Science',       'M.Sc Physics',        '2020-04-01', 'active'),
(4, 'TCH-1002', 'Ayesha',   'Siddiqui','ayesha@providence.edu.pk','0311-9876543', 'female', 'Mathematics',   'M.Sc Mathematics',    '2021-08-15', 'active');

-- Students
INSERT INTO students (user_id, registration_number, first_name, last_name, email, phone, gender, dob, class_id, section_id, session_id, roll_number, status, admission_date) VALUES
(5, 'STD-2001', 'Ali',     'Khan',   'ali@student.ps',    '0300-0000001', 'male',   '2010-05-15', 6,  1, 1, '01', 'active', '2025-04-01'),
(6, 'STD-2002', 'Zainab',  'Bilal',  'zainab@student.ps', '0300-0000002', 'female', '2009-08-22', 9,  3, 1, '01', 'active', '2025-04-01'),
(7, 'STD-2003', 'Hassan',  'Malik',  'hassan@student.ps', '0300-0000003', 'male',   '2008-11-10', 10, 6, 1, '02', 'active', '2025-04-01');

-- Parents
INSERT INTO parents (user_id, first_name, last_name, relation, email, phone, occupation, status) VALUES
(8, 'Ahmad',  'Khan',  'father', 'ahmad@parent.ps', '0300-1111111', 'Business',    'active'),
(NULL, 'Bilal', 'Ahmed', 'father', 'bilal@parent.ps', '0300-2222222', 'Government',  'active');

INSERT INTO parent_students (parent_id, student_id) VALUES (1, 1), (2, 2);

-- Notices
INSERT INTO notices (title, content, audience, priority, is_published, publish_date, created_by) VALUES
('Summer Vacation Announcement', 'School will remain closed from June 15th to August 14th, 2026. All students are advised to complete their holiday assignments during this period.', 'all', 'urgent', 1, '2026-06-08', 1),
('Fee Submission Deadline',      'Please submit Term 2 fees before June 10th to avoid fine. Last date is strictly enforced.', 'all', 'important', 1, '2026-06-07', 1),
('Annual Sports Day',            'Registrations are now open for the Annual Sports Gala 2026. Contact your class teacher for details.', 'students', 'normal', 1, '2026-06-05', 1);

-- Events
INSERT INTO events (title, description, event_date, start_time, end_time, venue, type, is_published, created_by) VALUES
('Annual Sports Day',   'Annual sports gala with various athletic events.',          '2026-06-20', '08:00:00', '14:00:00', 'School Ground',   'sports',   1, 1),
('Mid-Term Exams',      'Mid-term examinations for all classes.',                    '2026-07-01', '08:00:00', '11:00:00', 'Examination Hall','exam',     1, 1),
('Parent-Teacher Meet', 'Quarterly parent-teacher meeting for progress discussion.', '2026-06-25', '10:00:00', '13:00:00', 'School Hall',     'academic', 1, 1);

-- Fee Structure
INSERT INTO fee_structures (name, class_id, session_id, amount, type, due_day, late_fee, is_active) VALUES
('Class 6 Monthly Fee',  6,  1, 2500.00, 'monthly', 10, 200.00, 1),
('Class 9 Monthly Fee',  9,  1, 3000.00, 'monthly', 10, 200.00, 1),
('Class 10 Monthly Fee', 10, 1, 3200.00, 'monthly', 10, 200.00, 1),
('Class 11 Monthly Fee', 11, 1, 3500.00, 'monthly', 10, 200.00, 1);

-- Fee Payments (sample)
INSERT INTO fee_payments (student_id, fee_structure_id, challan_no, amount, total_paid, payment_date, due_date, payment_method, status, month_year, collected_by) VALUES
(1, 1, 'CH-2026-001', 2500.00, 2500.00, '2026-06-05', '2026-06-10', 'cash', 'paid',   '2026-06', 1),
(2, 2, 'CH-2026-002', 3000.00, 3000.00, '2026-06-06', '2026-06-10', 'bank', 'paid',   '2026-06', 1),
(3, 3, 'CH-2026-003', 3200.00, 0.00,    NULL,          '2026-06-10', 'cash', 'unpaid', '2026-06', NULL);

-- Library Books
INSERT INTO books (title, author, isbn, publisher, category, total_copies, available_copies, shelf_location) VALUES
('Mathematics Class 9',   'Punjab Textbook Board', '978-969-0-01',  'PTB',        'Textbook', 5, 4, 'A-01'),
('English Grammar',       'Raymond Murphy',        '978-0-521-53',  'Cambridge',  'Reference', 3, 3, 'B-02'),
('Physics Practical',     'Dr. S. M. Feroze',      '978-969-0-05',  'Royal Books','Science',  4, 3, 'C-01'),
('Urdu Adab',             'Dr. Anwar Sadeed',      '978-969-416',   'Maktaba',    'Urdu',     2, 2, 'D-05');

-- Vehicles
INSERT INTO vehicles (registration_no, make_model, capacity, driver_name, driver_phone, status) VALUES
('LEA-001', 'Toyota Coaster 2020', 30, 'Muhammad Arif',  '0303-1111111', 'active'),
('LEA-002', 'Hino Minibus 2019',   45, 'Ghulam Hussain', '0303-2222222', 'active');

-- Routes
INSERT INTO routes (name, vehicle_id, stops, fare, status) VALUES
('Route A - Model Town',  1, 'Model Town, Garden Town, Gulberg, School',           800.00, 'active'),
('Route B - DHA',         2, 'DHA Phase 1, DHA Phase 2, Cantt, School',            1000.00, 'active');

-- Exams
INSERT INTO exams (name, type, class_id, session_id, start_date, end_date, total_marks, passing_marks, status) VALUES
('Mid-Term Exam 2026',  'mid_term', 9,  1, '2026-07-01', '2026-07-10', 100, 40, 'upcoming'),
('Final Exam 2026',     'final',    10, 1, '2026-09-01', '2026-09-15', 100, 40, 'upcoming');

-- Timetable (sample periods for Class 9 Section A, Monday)
INSERT INTO timetable (class_id, section_id, subject_id, teacher_id, day, period_no, start_time, end_time, room, session_id) VALUES
(9, 3, 1, 1, 'Monday', 1, '08:00:00', '08:40:00', 'Room 9A', 1),
(9, 3, 2, 2, 'Monday', 2, '08:40:00', '09:20:00', 'Room 9A', 1),
(9, 3, 3, 1, 'Monday', 3, '09:20:00', '10:00:00', 'Room 9A', 1),
(9, 3, 4, 2, 'Monday', 4, '10:30:00', '11:10:00', 'Room 9A', 1),
(9, 3, 5, 1, 'Monday', 5, '11:10:00', '11:50:00', 'Room 9A', 1),
(9, 3, 6, 2, 'Monday', 6, '11:50:00', '12:30:00', 'Room 9A', 1);

-- Attendance (sample)
INSERT INTO attendance (student_id, class_id, section_id, date, status, marked_by) VALUES
(1, 6,  1, '2026-06-08', 'present', 1),
(2, 9,  3, '2026-06-08', 'present', 1),
(3, 10, 6, '2026-06-08', 'absent',  1),
(1, 6,  1, '2026-06-07', 'present', 1),
(2, 9,  3, '2026-06-07', 'late',    1),
(3, 10, 6, '2026-06-07', 'present', 1);

-- System Settings
INSERT INTO settings (setting_key, setting_val) VALUES
('school_name',      'The Providence School'),
('school_address',   '123 Main Street, Lahore, Pakistan'),
('school_phone',     '042-35761234'),
('school_email',     'info@providence.edu.pk'),
('school_website',   'www.providence.edu.pk'),
('school_logo',      'assets/images/logo.png'),
('academic_year',    '2025-2026'),
('currency',         'PKR'),
('date_format',      'd M, Y'),
('sms_enabled',      '0'),
('email_enabled',    '0'),
('timezone',         'Asia/Karachi');
