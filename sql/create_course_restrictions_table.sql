CREATE TABLE IF NOT EXISTS event_course_restrictions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    course VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES event_table(number) ON DELETE CASCADE,
    UNIQUE KEY unique_event_course (event_id, course)
); 