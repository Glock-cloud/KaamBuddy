# KaamChaahiye - Service Provider Platform

KaamChaahiye (meaning "Need Work" in Hindi) is a web-based platform that connects skilled service providers with users seeking their services. The platform allows service providers to register, showcase their work, and receive reviews, while users can search for providers based on location and service type.

## Features

### For Service Providers
- Registration with personal details and service offerings
- Profile photo upload
- Portfolio/work image uploads
- Contact information (phone and WhatsApp)
- Receive ratings and reviews from clients

### For Users/Clients
- Search for service providers by location and service type
- View provider profiles, work samples, and reviews
- Contact providers directly via phone or WhatsApp
- Submit ratings, reviews, and images of completed work

## Technologies Used
- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Image Storage**: File system with URL references in database

## Installation and Setup

### Prerequisites
- XAMPP (or any PHP development environment with MySQL)
- Web browser

### Steps
1. Clone or download the repository to your XAMPP's htdocs folder
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `includes/db_connect.php` if needed
4. Access the application via your localhost

```sql
-- Database setup (can be run directly or imported)
CREATE DATABASE IF NOT EXISTS kaamchaahiye;
```

### Directory Structure
```
kaamchaahiye/
├── css/
│   └── style.css
├── js/
│   └── script.js
├── includes/
│   ├── db_connect.php
│   └── functions.php
├── uploads/
│   ├── profiles/
│   ├── work/
│   └── reviews/
├── database/
│   └── schema.sql
├── index.php
├── register.php
├── search.php
├── provider.php
└── README.md
```

## Usage

### For Service Providers
1. Navigate to the "Register as Provider" page
2. Fill in your details, services offered, and contact information
3. Upload a profile photo and work samples
4. Submit the form to create your profile

### For Users
1. Use the search function on the homepage to find service providers
2. Filter by location and service type
3. Browse provider profiles, view their portfolio and reviews
4. Contact providers directly through WhatsApp or phone call
5. After service completion, leave a review and rating

## Security Features
- Input sanitization
- Image validation
- MySQL injection protection

## Future Enhancements
- User authentication and profiles
- Service booking system
- Payment integration
- Advanced search filters
- Service categories and subcategories
- Mobile application

## License
This project is created for demonstration purposes. Feel free to use and modify for your own projects.

## Credits
- Placeholder images: [via.placeholder.com](https://via.placeholder.com)
- Icons: [Font Awesome](https://fontawesome.com/) 