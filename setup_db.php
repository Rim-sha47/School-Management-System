<?php
require_once 'c:/xampp/htdocs/School_Management_System/config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS timetables (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        section_id INT DEFAULT NULL,
        subject_id INT NOT NULL,
        teacher_id INT NOT NULL,
        day_of_week VARCHAR(20) NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        room_number VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'timetables' created successfully.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
