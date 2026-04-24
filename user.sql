CREATE TABLE users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL
);

CREATE TABLE user_packages (
    pid INT AUTO_INCREMENT PRIMARY KEY,
    packageID INT NOT NULL,
    userID INT NOT NULL,
    category VARCHAR(255),
    name VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    status ENUM('Pending', 'Processing', 'Active', 'Failed') DEFAULT 'Pending'
);

CREATE TABLE payments (
    paymentID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    packageID INT NOT NULL,
    order_id VARCHAR(50) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(30) NULL
);

CREATE TABLE bmi (
    bmiID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    height DECIMAL(5,2) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    bmi_value DECIMAL(5,2) NOT NULL,
    bmi_category ENUM('Underweight', 'Normal', 'Overweight', 'Obese') NOT NULL
);

CREATE TABLE feedback (
    feedbackID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    rating INT NULL,
    comments TEXT NULL
);