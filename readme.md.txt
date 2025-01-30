# Event Management System

A simple web-based event management system built with PHP, Bootstrap, and MySQL that allows admins to manage events and attendees to register for events.

## Features

### For Attendees
- View all available events on the homepage
- Register for events through a simple form
- Automatic capacity checking to prevent overbooking

### For Admins
- Secure login and registration system
- Create, edit, and delete events
- View event details including registered attendees
- Download attendee lists in CSV format
- Dashboard with event management capabilities

## Requirements

- PHP 
- MySQL
- Apache web server
- PDO PHP Extension
- MySQL PHP Extension

## Installation

1. Clone the repository to your web server directory:
   
   --> git clone [https://github.com/ammaribnmohd/event-management-system]
   


2. Import the database schema:
   - Find the `schema.sql` file in the schema folder
   - Import it using phpMyAdmin or MySQL command line:s
     -->  mysql -u root -p event_management < schema.sql
  

3. Configure database connection:
   - Navigate to `config/database.php`
   - Update the following constants with your database credentials:
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'event_management');

4. Create an admin account:
   - Visit `http://localhost/event_management/index.php`
   - Register your admin account
   - Use the credentials to login at `http://localhost/event_management/admin/auth/login.php`

## Usage

### For Attendees
1. Visit the homepage
2. Browse available events
3. Click on an event to view details
4. Fill out the registration form to book the event
   - Registration will be blocked if event is at maximum capacity

### For Admins
1. Click "Login" on the homepage
2. After logging in, you can:
   - View all events in the dashboard
   - Add new events
   - Edit existing events
   - Delete events
   - View registered attendees
   - Download attendee lists


## Security Features
- Password hashing for admin accounts
- PDO prepared statements to prevent SQL injection
- Input validation on both client and server side
- Session-based authentication for admin area

