# STP Website - Project Structure

## Overview
This is a well-organized web project for the Glo-CED India website with a clean folder structure separating concerns.

## Folder Structure

-### `/pages/`
Contains all public-facing HTML pages:
- `index.html` - Homepage
- `pages/glo-ced.html` - About Us / Glo-CED information page
- `programs.html` - Programs listing page
- `program.html` - Individual program detail page
- `gallery.html` - Gallery page
- `blog.html` - Blog listings page
- `blog_details.html` - Individual blog post page
- `contact_new.html` - Contact form page (modern)
- `contact.html` - Alternative contact page
- `elements.html` - UI elements/components page
- `main.html` - Main page
- `pages/glo-ced.html` - Glo-CED information page

### `/admin/`
Admin panel and management files:
- `admin_users.php` - User management system
- `admin_submissions.php` - Submission management
- `admin_manage_users.php` - User administration
- `admin_get_submission.php` - Fetch submissions
- `admins_table_migration.sql` - Database migration

### `/backend/`
Server-side processing and API endpoints:
- `contact_process.php` - Handles contact form submissions
- `auth_status.php` - Authentication status checking

### `/config/`
Configuration and database files:
- `db_config.php` - Database connection configuration
- `database_schema.sql` - Database schema definition

### `/assets/`
Static assets and resources:
- `/css/` - Stylesheets
- `/js/` - JavaScript files
- `/img/` - Images (blog, gallery, heroes, logos, posts)
- `/fonts/` - Font files
- `/scss/` - SCSS source files
- `/main/` - Additional content

### `/Doc/`
Documentation and supplementary materials:
- Contains documentation files and resources

### Other Files
- `index.php` - Root entry point for PHP routing
- `.htaccess` - Apache configuration for URL rewriting
- `readme.txt` - Original readme
- `/removed.file/` - Archive of removed features

## How the Website Works

### Public Access
1. User visits the website (e.g., `http://localhost/STP/`)
2. Server loads `index.php` or serves `pages/index.html`
3. All navigation links point to other pages in `/pages/`
4. Assets (CSS, JS, images) are referenced via `../assets/`

### Contact Form Processing
1. User submits form on `pages/contact_new.html`
2. Form posts to `../backend/contact_process.php`
3. Backend validates and processes the data

### Admin Panel
1. Accessible via admin folder files
2. Requires authentication
3. Includes user and submission management

## File Access Paths

From **pages/** folder:
```
- ../assets/css/style.css  → CSS
- ../assets/img/logo.png   → Images
- ../backend/contact_process.php → Backend processing
- pages/glo-ced.html → About / Organization information
```

From **admin/** folder:
```
- ../config/db_config.php  → Database config
- admin_users.php          → Local includes
```

From **backend/** folder:
```
- ../config/db_config.php  → Database config
```

## Key Features

✅ Organized by functionality
✅ Clear separation of concerns
✅ Security: Backend and config folders protected
✅ Scalable: Easy to add new pages or features
✅ Clean URLs: Apache rewriting for pretty URLs
✅ Assets management: Centralized CSS, JS, and images

## Setup Requirements

1. Apache server with `mod_rewrite` enabled
2. PHP 7.0+ with MySQL/MySQLi support
3. Database configured in `/config/db_config.php`

## Development Notes

- Update relative paths when moving files between folders
- External resources (Tailwind CSS, FontAwesome) use CDN - no local copies needed
- PHP includes use `require_once` for configuration files
- All page-to-page navigation uses simple HTML links (same folder)

## Additional Files

- `database_schema.sql` - SQL to set up the database structure
- `admins_table_migration.sql` - SQL for admin user table
