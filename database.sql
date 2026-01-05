-- Hotel Management System Database Schema
CREATE DATABASE IF NOT EXISTS hotel_management;
USE hotel_management;

-- Room Types Table
CREATE TABLE room_types (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            type_name VARCHAR(100) NOT NULL,
                            description TEXT,
                            price_per_night DECIMAL(10, 2) NOT NULL,
                            capacity INT NOT NULL,
                            amenities TEXT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rooms Table
CREATE TABLE rooms (
                       id INT PRIMARY KEY AUTO_INCREMENT,
                       room_number VARCHAR(20) NOT NULL UNIQUE,
                       room_type_id INT NOT NULL,
                       floor INT,
                       status ENUM('available', 'occupied', 'maintenance', 'cleaning') DEFAULT 'available',
                       features TEXT,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
);

-- Guests Table
CREATE TABLE guests (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        first_name VARCHAR(100) NOT NULL,
                        last_name VARCHAR(100) NOT NULL,
                        email VARCHAR(255) UNIQUE,
                        phone VARCHAR(20) NOT NULL,
                        address TEXT,
                        id_type ENUM('passport', 'driver_license', 'national_id', 'voter_id') DEFAULT 'national_id',
                        id_number VARCHAR(100),
                        country VARCHAR(100) DEFAULT 'Ghana',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE bookings (
                          id INT PRIMARY KEY AUTO_INCREMENT,
                          guest_id INT NOT NULL,
                          room_id INT NOT NULL,
                          check_in DATE NOT NULL,
                          check_out DATE NOT NULL,
                          adults INT DEFAULT 1,
                          children INT DEFAULT 0,
                          special_requests TEXT,
                          status ENUM('confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show') DEFAULT 'confirmed',
                          total_amount DECIMAL(10, 2) NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
                          FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Payments Table
CREATE TABLE payments (
                          id INT PRIMARY KEY AUTO_INCREMENT,
                          booking_id INT NOT NULL,
                          amount DECIMAL(10, 2) NOT NULL,
                          payment_method ENUM('cash', 'mobile_money', 'credit_card', 'bank_transfer') DEFAULT 'cash',
                          transaction_id VARCHAR(100),
                          payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
                          notes TEXT,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO room_types (type_name, description, price_per_night, capacity, amenities) VALUES
                                                                                          ('Standard Room', 'Comfortable room with basic amenities', 250.00, 2, 'WiFi, TV, Air Conditioning'),
                                                                                          ('Deluxe Room', 'Spacious room with premium features', 400.00, 3, 'WiFi, TV, Air Conditioning, Mini Bar, Balcony'),
                                                                                          ('Executive Suite', 'Luxury suite with separate living area', 750.00, 4, 'WiFi, TV, Air Conditioning, Mini Bar, Jacuzzi, Kitchenette'),
                                                                                          ('Family Room', 'Large room suitable for families', 550.00, 5, 'WiFi, TV, Air Conditioning, Extra Beds');

INSERT INTO rooms (room_number, room_type_id, floor, status) VALUES
                                                                 ('101', 1, 1, 'available'),
                                                                 ('102', 1, 1, 'available'),
                                                                 ('103', 1, 1, 'available'),
                                                                 ('201', 2, 2, 'available'),
                                                                 ('202', 2, 2, 'available'),
                                                                 ('301', 3, 3, 'available'),
                                                                 ('302', 4, 3, 'available');

INSERT INTO guests (first_name, last_name, email, phone, address, id_type, id_number, country) VALUES
                                                                                                   ('Kwame', 'Amponsah', 'kwame@example.com', '0551234567', 'Accra Central', 'national_id', 'GHA-001', 'Ghana'),
                                                                                                   ('Ama', 'Mensah', 'ama@example.com', '0549876543', 'Kumasi', 'passport', 'P123456', 'Ghana'),
                                                                                                   ('Kofi', 'Asante', 'kofi@example.com', '0207654321', 'Takoradi', 'driver_license', 'DL7890', 'Ghana'),
                                                                                                   ('Esi', 'Boateng', 'esi@example.com', '0271112233', 'Cape Coast', 'voter_id', 'VID12345', 'Ghana');

-- Create indexes
CREATE INDEX idx_room_status ON rooms(status);
CREATE INDEX idx_booking_dates ON bookings(check_in, check_out);
CREATE INDEX idx_guest_email ON guests(email);
CREATE INDEX idx_booking_guest ON bookings(guest_id);
CREATE INDEX idx_payment_booking ON payments(booking_id);