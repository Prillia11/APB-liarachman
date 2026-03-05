# Prillia Digital Library System

Web-based library management system built with PHP Native and MySQL.

## Features

### 👤 Admin
- **Dashboard**: Quick stats overview and recent transactions.
- **Book Management**: Full CRUD (Create, Read, Update, Delete) with search.
- **Member Management**: Manage student accounts and registration.
- **Transaction Management**: Record borrowings, track returns, and manage fines.
- **Advanced Search**: Filter data by multiple categories.

### 👨‍🎓 User (Student)
- **Self Registration**: Students can create their own accounts.
- **Catalog**: Search and explore book collection with real-time stock status.
- **Borrowing**: Easy one-click borrowing process.
- **Return Tracking**: Monitor active loans and return history.
- **Fine System**: Automated calculation for late returns (after 7 days).

## Technical Specifications
- **Backend**: PHP 7.4+ (using PDO for security).
- **Database**: MySQL.
- **Frontend**: Modern CSS3 (Vanilla) with Responsive Design.
- **Security**: 
  - Password hashing (BCRYPT).
  - Role-based Access Control (RBAC).
  - SQL Injection prevention via Prepared Statements.
  - Session security.

## Installation
1. Move the `APB-Prillia` folder to `xampp/htdocs/`.
2. Open PHPMyAdmin and create a database named `db_perpustakaan_prillia`.
3. Import the `database.sql` file.
4. Access via browser: `http://localhost/APB-Prillia/`.

## Credentials
- **Admin**: 
  - Username: `admin`
  - Password: `admin123`
- **Student**: Register a new account via the registration page.
