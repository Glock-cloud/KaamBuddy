-- Create service categories table
CREATE TABLE service_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default service categories
INSERT INTO service_categories (name, icon, description) VALUES
('Plumbing', 'fa-wrench', 'Plumbing repairs, installations, and maintenance'),
('Electrical', 'fa-bolt', 'Electrical wiring, repairs, and installations'),
('Carpentry', 'fa-hammer', 'Woodwork, furniture repair, and installations'),
('Painting', 'fa-paint-roller', 'Interior and exterior painting services'),
('Cleaning', 'fa-broom', 'Home and office cleaning services'),
('Beauty', 'fa-cut', 'Haircuts, makeup, and styling services'),
('Tutoring', 'fa-book', 'Academic and skill-based tutoring'),
('Cooking', 'fa-utensils', 'Home cooking and catering services');

-- Modify service_providers table to include location data
ALTER TABLE service_providers
ADD COLUMN category_id INT,
ADD COLUMN address TEXT,
ADD COLUMN latitude DECIMAL(10, 8),
ADD COLUMN longitude DECIMAL(11, 8),
ADD FOREIGN KEY (category_id) REFERENCES service_categories(id); 