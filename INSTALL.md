# Installation Instructions

## Requirements
*   **PHP Version**: 7.4 or higher
*   **Database**: MySQL 5.7+ or MariaDB 10+
*   **Web Server**: Apache (with mod_rewrite enabled)
*   **Extensions**: PDO, GD, mbstring, json, cURL

## Setup Steps

### 1. File Upload
Upload all files to your web server (e.g., `public_html` folder).

### 2. Database Setup
1.  Create a new MySQL database (e.g., `mido_db`).
2.  Import the `database.sql` file into your new database via phpMyAdmin or command line.

### 3. Configuration
1.  Open `includes/config.php`.
2.  Update the database credentials:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'your_database_name');
    define('DB_USER', 'your_database_user');
    define('DB_PASS', 'your_database_password');
    ```
3.  Update the `SITE_URL` to match your domain:
    ```php
    define('SITE_URL', 'https://yourdomain.com');
    ```
4.  (Optional) Update mail settings if you plan to use SMTP.

### 4. Permissions
Ensure the `uploads/` directory and its subdirectories are writable (CHMOD 755 or 777).

## Access
*   **Website**: `https://yourdomain.com`
*   **Admin Panel**: `https://yourdomain.com/admin`
    *   **Username**: `admin`
    *   **Password**: `admin123`

## Troubleshooting
*   **404 Errors**: Ensure `.htaccess` is uploaded and mod_rewrite is enabled.
*   **Database Error**: Check credentials in `config.php`.
*   **Images Not Loading**: Check folder permissions for `uploads`.
*   **Admin Login Loop**: Verify session paths on your server are writable.
