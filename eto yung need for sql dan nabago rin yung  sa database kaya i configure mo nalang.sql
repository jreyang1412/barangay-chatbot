-- Database Schema for Barangay Chatbot System
-- Run this SQL script to create the necessary tables

CREATE DATABASE barangay_chatbot;
USE barangay_chatbot;

-- Users table (residents and barangay officials)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_type ENUM('resident', 'barangay_official') NOT NULL,
    phone_number VARCHAR(15),
    address TEXT,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    is_online BOOLEAN DEFAULT FALSE,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Chat conversations
CREATE TABLE conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    barangay_official_id INT NULL,
    conversation_type ENUM('bot', 'human', 'emergency') DEFAULT 'bot',
    status ENUM('active', 'resolved', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (barangay_official_id) REFERENCES users(id)
);

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_text TEXT NOT NULL,
    message_type ENUM('text', 'bot_option', 'location', 'emergency') DEFAULT 'text',
    bot_option_id VARCHAR(50) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id),
    FOREIGN KEY (sender_id) REFERENCES users(id)
);

-- Bot responses and options
CREATE TABLE bot_options (
    id VARCHAR(50) PRIMARY KEY,
    parent_id VARCHAR(50) NULL,
    option_text VARCHAR(255) NOT NULL,
    response_text TEXT NOT NULL,
    has_children BOOLEAN DEFAULT FALSE,
    option_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);

-- Emergency reports
CREATE TABLE emergency_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    conversation_id INT NOT NULL,
    emergency_type VARCHAR(100) DEFAULT 'General Emergency',
    location_description TEXT,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    status ENUM('pending', 'responding', 'resolved') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'high',
    assigned_official_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id),
    FOREIGN KEY (assigned_official_id) REFERENCES users(id)
);

-- Insert default bot options
INSERT INTO bot_options (id, parent_id, option_text, response_text, has_children, option_order) VALUES
-- Main menu
('main_menu', NULL, 'Main Menu', 'Welcome to Barangay Services! How can I help you today?', TRUE, 0),
('barangay_problems', 'main_menu', '🏢 Barangay Related Problems', 'What kind of barangay issue would you like to report?', TRUE, 1),
('livelihood_problems', 'main_menu', '💼 Livelihood Problems', 'I can help you with livelihood concerns. What specific issue are you facing?', TRUE, 2),
('emergency', 'main_menu', '🚨 Emergency', 'EMERGENCY ALERT ACTIVATED! Your location is being sent to barangay officials. Help is on the way!', FALSE, 3),

-- Barangay Problems submenu
('road_issues', 'barangay_problems', '🛣️ Road/Infrastructure Issues', 'I understand you have road or infrastructure concerns. Please describe the specific problem (potholes, broken streetlights, damaged sidewalks, etc.) and I will forward this to our Public Works team for immediate attention.', FALSE, 1),
('garbage_issues', 'barangay_problems', '🗑️ Garbage/Sanitation Issues', 'Thank you for reporting a sanitation issue. Please provide details about the garbage collection problem, clogged drainage, or other sanitation concerns. Our Environmental Services team will address this within 24-48 hours.', FALSE, 2),
('noise_complaints', 'barangay_problems', '🔊 Noise/Community Disturbance', 'I will help you with noise complaints or community disturbances. Please provide the location and nature of the disturbance. Our Community Relations team will investigate and take appropriate action according to barangay ordinances.', FALSE, 3),

-- Livelihood Problems submenu
('job_assistance', 'livelihood_problems', '👷 Job/Employment Assistance', 'Our Livelihood Office can help connect you with job opportunities! We have partnerships with local businesses and skills training programs. Please tell me about your skills, experience, or what type of work you are looking for.', FALSE, 1),
('business_permits', 'livelihood_problems', '📋 Business Permits & Registration', 'I can guide you through the business permit process. For new businesses, you will need: Barangay Business Clearance, DTI Registration, BIR TIN, and Mayor\'s Permit. The total processing time is usually 7-14 days. Would you like specific requirements for your business type?', FALSE, 2),
('financial_assistance', 'livelihood_problems', '💰 Financial Assistance Programs', 'Our barangay offers several financial assistance programs: Micro-lending for small businesses, Educational assistance, Medical assistance, and Emergency financial aid. Each program has specific requirements. Which type of assistance do you need?', FALSE, 3);

-- Insert sample users (password is 'password123' hashed)
INSERT INTO users (username, password, full_name, user_type, phone_number, address) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Barangay Captain', 'barangay_official', '09123456789', 'Barangay Hall'),
('official1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kagawad Juan', 'barangay_official', '09123456790', 'Barangay Hall'),
('resident1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Santos', 'resident', '09123456791', 'Purok 1, Sample Street');







---

CREATE DIRECTORY STRUCTURE:
/barangay_chatbot/
  ├── index.php
  ├── login.php  
  ├── register.php
  ├── logout.php
  ├── resident_chat.php
  ├── official_dashboard.php
  ├── config/
  │   ├── database.php
  │   └── session.php
  ├── classes/
  │   └── Chatbot.php
  └── ajax/
      ├── send_message.php
      ├── handle_bot_choice.php
      ├── get_messages.php
      ├── get_conversations.php
      ├── send_official_message.php
      └── update_conversation_status.php