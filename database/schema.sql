-- Create database
CREATE DATABASE IF NOT EXISTS kaamchaahiye;
USE kaamchaahiye;

-- Service providers table
CREATE TABLE IF NOT EXISTS service_providers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    services TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    profile_image_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Work images table
CREATE TABLE IF NOT EXISTS work_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    provider_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (provider_id) REFERENCES service_providers(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    provider_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    review_image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES service_providers(id) ON DELETE CASCADE
);

-- Add indexes for faster queries
CREATE INDEX idx_provider_location ON service_providers(location);
CREATE INDEX idx_provider_services ON service_providers(services(255));
CREATE INDEX idx_work_provider ON work_images(provider_id);
CREATE INDEX idx_review_provider ON reviews(provider_id); 