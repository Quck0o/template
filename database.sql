DROP DATABASE IF EXISTS college_system;
CREATE DATABASE college_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE college_system;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'staff') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    head_teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_teacher_id) REFERENCES users(id)
);

CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    department_id INT,
    course INT NOT NULL,
    curator_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (curator_id) REFERENCES users(id)
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    hours INT NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT,
    subject_id INT,
    teacher_id INT,
    day_of_week INT NOT NULL,
    lesson_number INT NOT NULL,
    room VARCHAR(20),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject_id INT,
    teacher_id INT,
    grade INT NOT NULL,
    grade_type ENUM('exam', 'test', 'attestation', 'coursework') NOT NULL,
    date_given DATE,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT,
    teacher_id INT,
    group_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    deadline DATE,
    max_score INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    FOREIGN KEY (group_id) REFERENCES groups(id)
);

CREATE TABLE student_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT,
    student_id INT,
    submitted_file VARCHAR(255),
    score INT,
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    category ENUM('general', 'academic', 'events', 'important') DEFAULT 'general',
    is_published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

INSERT INTO users (username, password, role, full_name, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Директор Иванова М.П.', 'director@college.ru'),
('teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Преподаватель Петров И.С.', 'petrov@college.ru'),
('teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Преподаватель Сидорова А.В.', 'sidorova@college.ru'),
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Студент Козлов Д.А.', 'student1@college.ru'),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Студент Новикова Е.С.', 'student2@college.ru'),
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Сотрудник библиотеки Васильева Т.И.', 'vasilieva@college.ru');

INSERT INTO departments (name, description, head_teacher_id) VALUES 
('Информационные технологии', 'Отделение программирования и сетевых технологий', 2),
('Экономика и бухгалтерский учет', 'Отделение экономики и финансов', 3);

INSERT INTO groups (name, department_id, course, curator_id) VALUES 
('ИТ-21', 1, 2, 2),
('ИТ-31', 1, 3, 2),
('ЭБ-21', 2, 2, 3);

INSERT INTO subjects (name, department_id, hours, semester) VALUES 
('Программирование на Python', 1, 120, 3),
('Базы данных', 1, 90, 4),
('Веб-разработка', 1, 110, 5),
('Экономическая теория', 2, 100, 3),
('Бухгалтерский учет', 2, 120, 4);

INSERT INTO schedules (group_id, subject_id, teacher_id, day_of_week, lesson_number, room) VALUES 
(1, 1, 2, 1, 1, 'А-101'),
(1, 1, 2, 3, 2, 'А-101'),
(2, 2, 2, 2, 3, 'Б-205'),
(3, 4, 3, 1, 1, 'В-301');

INSERT INTO grades (student_id, subject_id, teacher_id, grade, grade_type, date_given) VALUES 
(4, 1, 2, 5, 'test', CURDATE()),
(5, 4, 3, 4, 'exam', CURDATE());

INSERT INTO news (title, content, author_id, category) VALUES 
('Начало нового учебного года', 'Дорогие студенты! Поздравляем с началом учебного года!', 1, 'important'),
('Конкурс программирования', 'Приглашаем всех желающих принять участие в конкурсе...', 2, 'events');