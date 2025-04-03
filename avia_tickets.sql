CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('User', 'Admin') NOT NULL DEFAULT 'User'
);

-- Insert sample data
INSERT INTO users (user_id, login, first_name, last_name, email, phone_number, password, role)
VALUES
    (1, 'admin', 'Admin', 'User', 'admin@example.com', '1234567890', 'admin_hashed_password', 'Admin'),
    (2, 'testuser', 'Test', 'User', 'test_user@example.com', '0987654321', 'test_user_hashed_password', 'User');

-- Table for cities
CREATE TABLE IF NOT EXISTS cities (
    city_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL
);

-- Table for airports
CREATE TABLE IF NOT EXISTS airports (
    airport_code VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city_id INT NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(city_id)
);

-- Table for airlines
CREATE TABLE IF NOT EXISTS airlines (
    airline_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    iata_code VARCHAR(10) UNIQUE NOT NULL
);

-- Junction table for airports and airlines (many-to-many)
CREATE TABLE IF NOT EXISTS airport_airlines (
    airport_code VARCHAR(10) NOT NULL,
    airline_id INT NOT NULL,
    PRIMARY KEY (airport_code, airline_id),
    FOREIGN KEY (airport_code) REFERENCES airports(airport_code),
    FOREIGN KEY (airline_id) REFERENCES airlines(airline_id)
);

-- Table for flights
CREATE TABLE IF NOT EXISTS flights (
    flight_id INT PRIMARY KEY AUTO_INCREMENT,
    flight_number VARCHAR(10) NOT NULL UNIQUE,
    departure_airport VARCHAR(10) NOT NULL,
    arrival_airport VARCHAR(10) NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    airline_id INT NOT NULL,
    aircraft_type VARCHAR(50),
    FOREIGN KEY (departure_airport) REFERENCES airports(airport_code),
    FOREIGN KEY (arrival_airport) REFERENCES airports(airport_code),
    FOREIGN KEY (airline_id) REFERENCES airlines(airline_id)
);

-- Table for bookings
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    booking_date DATETIME NOT NULL,
    flight_id INT NOT NULL,
    status ENUM('Confirmed', 'Pending', 'Cancelled') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id)
);

-- Table for tickets
CREATE TABLE IF NOT EXISTS tickets (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    flight_id INT NOT NULL,
    seat_number VARCHAR(5) NOT NULL,
    ticket_class ENUM('Economy', 'Business', 'First') NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id)
);

-- Table for payments
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATETIME NOT NULL,
    status ENUM('Completed', 'Failed', 'Pending') NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- Table for luggage
CREATE TABLE IF NOT EXISTS luggage (
    luggage_id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    weight DECIMAL(5, 2) NOT NULL,
    type ENUM('Checked-in', 'Hand') NOT NULL,
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id)
);

-- Table for messages
CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert data into cities table
INSERT INTO cities (name, country)
VALUES
('London', 'UK'),
('Paris', 'France'),
('Frankfurt', 'Germany'),
('Amsterdam', 'Netherlands'),
('Madrid', 'Spain'),
('Rome', 'Italy'),
('Prague', 'Czech Republic'),
('Vienna', 'Austria'),
('Brussels', 'Belgium'),
('Warsaw', 'Poland');

-- Insert data into airports table
INSERT INTO airports (airport_code, name, city_id)
VALUES
('FRA', 'Frankfurt Airport', 3),
('CGH', 'Congonhas Airport', 2),
('MAD', 'Madrid-Barajas Airport', 5),
('AMS', 'Amsterdam Airport Schiphol', 4),
('LHR', 'Heathrow Airport', 1),
('CDG', 'Charles de Gaulle Airport', 2),
('ROM', 'Rome Fiumicino Airport', 6),
('PRG', 'Václav Havel Airport Prague', 7),
('BRU', 'Brussels Airport', 9),
('VIE', 'Vienna International Airport', 8),
('WAW', 'Warsaw Chopin Airport', 10);

-- Insert data into airlines table
INSERT INTO airlines (name, iata_code)
VALUES
('British Airways', 'BA'),
('Air France', 'AF'),
('Lufthansa', 'LH'),
('KLM', 'KL'),
('Iberia', 'IB'),
('Alitalia', 'AZ'),
('Czech Airlines', 'OK'),
('Austrian Airlines', 'OS'),
('Brussels Airlines', 'SN'),
('LOT Polish Airlines', 'LO');

-- Insert data into airport_airlines (many-to-many) table
INSERT INTO airport_airlines (airport_code, airline_id)
VALUES
('LHR', 1), ('LHR', 2), ('LHR', 3),
('CDG', 2), ('CDG', 3), ('CDG', 4),
('FRA', 3), ('FRA', 5),
('AMS', 4), ('AMS', 5),
('MAD', 5), ('MAD', 6),
('ROM', 6), ('ROM', 7),
('PRG', 7), ('PRG', 1),
('VIE', 8), ('VIE', 9),
('BRU', 9), ('BRU', 10),
('WAW', 10), ('WAW', 3);

-- Insert data into flights table
INSERT INTO flights (flight_number, departure_airport, arrival_airport, departure_time, arrival_time, airline_id, aircraft_type)
VALUES
('FL119', 'FRA', 'CGH', '2025-01-27 09:00:00', '2025-01-27 12:00:00', 3, 'A321'),
('FL120', 'MAD', 'AMS', '2025-01-21 06:00:00', '2025-01-21 09:00:00', 4, 'B737'),
('FL123', 'LHR', 'CDG', '2025-01-10 08:00:00', '2025-01-10 09:30:00', 1, 'A320'),
('FL124', 'CDG', 'LHR', '2025-01-10 15:00:00', '2025-01-10 16:30:00', 2, 'B737'),
('FL125', 'LHR', 'FRA', '2025-01-11 09:00:00', '2025-01-11 11:00:00', 1, 'A320'),
('FL126', 'AMS', 'MAD', '2025-01-12 06:00:00', '2025-01-12 09:00:00', 4, 'B737'),
('FL127', 'MAD', 'AMS', '2025-01-12 17:00:00', '2025-01-12 20:00:00', 5, 'A321'),
('FL128', 'LHR', 'ROM', '2025-01-13 08:00:00', '2025-01-13 11:00:00', 6, 'A320'),
('FL129', 'ROM', 'LHR', '2025-01-13 19:00:00', '2025-01-13 22:00:00', 6, 'A320'),
('FL130', 'CDG', 'AMS', '2025-01-14 10:00:00', '2025-01-14 11:30:00', 2, 'E190'),
('FL131', 'AMS', 'CDG', '2025-01-14 15:00:00', '2025-01-14 16:30:00', 4, 'E190'),
('FL132', 'LHR', 'PRG', '2025-01-15 07:00:00', '2025-01-15 09:30:00', 7, 'A319'),
('FL133', 'PRG', 'LHR', '2025-01-15 19:00:00', '2025-01-15 21:30:00', 7, 'A319'),
('FL134', 'BRU', 'VIE', '2025-01-16 09:00:00', '2025-01-16 12:00:00', 9, 'Q400'),
('FL135', 'VIE', 'BRU', '2025-01-16 18:00:00', '2025-01-16 21:00:00', 8, 'A320'),
('FL136', 'WAW', 'FRA', '2025-01-17 06:00:00', '2025-01-17 08:30:00', 10, 'B737'),
('FL137', 'FRA', 'WAW', '2025-01-17 18:00:00', '2025-01-17 20:30:00', 3, 'A320'),
('FL138', 'MAD', 'FRA', '2025-01-18 09:00:00', '2025-01-18 12:00:00', 5, 'A321'),
('FL139', 'FRA', 'MAD', '2025-01-18 16:00:00', '2025-01-18 19:00:00', 3, 'A320'),
('FL140', 'CDG', 'LHR', '2025-01-19 10:00:00', '2025-01-19 11:30:00', 2, 'B737');

-- Archive table for flights
CREATE TABLE IF NOT EXISTS flights_archive (
    flight_id INT PRIMARY KEY,
    flight_number VARCHAR(10) NOT NULL,
    departure_airport VARCHAR(10) NOT NULL,
    arrival_airport VARCHAR(10) NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    airline_id INT NOT NULL,
    aircraft_type VARCHAR(50),
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departure_airport) REFERENCES airports(airport_code),
    FOREIGN KEY (arrival_airport) REFERENCES airports(airport_code),
    FOREIGN KEY (airline_id) REFERENCES airlines(airline_id)
);

-- Archive table for users
CREATE TABLE IF NOT EXISTS users_archive (
    user_id INT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('User', 'Admin') NOT NULL DEFAULT 'User',
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
