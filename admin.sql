CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE diet_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bmi_category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE membership_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    description TEXT NULL
);

CREATE TABLE workout_plans (
    wid INT AUTO_INCREMENT PRIMARY KEY,
    id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    exercises TEXT NOT NULL
);