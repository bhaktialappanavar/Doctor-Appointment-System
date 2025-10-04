-- Professional Doctor Appointment System Database
CREATE DATABASE IF NOT EXISTS doc_appointment_pro;
USE doc_appointment_pro;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'admin') NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Specialties table
CREATE TABLE specialties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50)
);

-- Doctor profiles
CREATE TABLE doctor_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    specialty_id INT,
    license_number VARCHAR(50),
    experience_years INT DEFAULT 0,
    qualification TEXT,
    bio TEXT,
    consultation_fee DECIMAL(10,2) DEFAULT 0,
    available_days JSON,
    available_hours JSON,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialty_id) REFERENCES specialties(id)
);

-- Patient profiles
CREATE TABLE patient_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    emergency_contact VARCHAR(20),
    blood_group VARCHAR(5),
    allergies TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Appointments
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    type ENUM('consultation', 'follow_up', 'emergency') DEFAULT 'consultation',
    symptoms TEXT,
    notes TEXT,
    fee DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Medical records
CREATE TABLE medical_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    diagnosis TEXT,
    treatment TEXT,
    prescription TEXT,
    notes TEXT,
    attachments JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
);

-- Insert sample data
INSERT INTO specialties (name, description, icon) VALUES 
('General Medicine', 'General medical practice and primary care', 'fas fa-stethoscope'),
('Cardiology', 'Heart and cardiovascular system specialist', 'fas fa-heartbeat'),
('Dermatology', 'Skin, hair, and nail conditions', 'fas fa-hand-paper'),
('Pediatrics', 'Medical care for children and infants', 'fas fa-baby'),
('Orthopedics', 'Bone, joint, and muscle disorders', 'fas fa-bone'),
('Neurology', 'Brain and nervous system disorders', 'fas fa-brain');

INSERT INTO users (email, password_hash, role, name, phone) VALUES 
('admin@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', '+1234567890'),
('dr.smith@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'Dr. John Smith', '+1234567891'),
('dr.johnson@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'Dr. Sarah Johnson', '+1234567892'),
('patient@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'John Doe', '+1234567893');

INSERT INTO doctor_profiles (user_id, specialty_id, license_number, experience_years, qualification, bio, consultation_fee, available_days, available_hours) VALUES 
(2, 1, 'MD12345', 10, 'MBBS, MD General Medicine', 'Experienced general practitioner with 10+ years of practice', 50.00, '["monday", "tuesday", "wednesday", "thursday", "friday"]', '["09:00", "10:00", "11:00", "14:00", "15:00", "16:00"]'),
(3, 2, 'MD12346', 8, 'MBBS, MD Cardiology', 'Specialist in cardiovascular diseases and heart conditions', 80.00, '["monday", "wednesday", "friday"]', '["10:00", "11:00", "14:00", "15:00"]');

INSERT INTO patient_profiles (user_id, date_of_birth, gender, address, blood_group) VALUES 
(4, '1990-05-15', 'male', '123 Main St, City', 'O+');