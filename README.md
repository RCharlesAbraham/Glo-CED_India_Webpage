# Glo-CED India Webpage

A full-stack web project with a plain HTML/CSS/JS frontend and a PHP backend, running on XAMPP (Apache + MySQL).

---

## Folder Structure

```
Glo-CED_India_Webpage/
│
├── client/                        # Frontend (HTML / CSS / JS)
│   ├── public/                    # Static assets served directly
│   │   ├── css/
│   │   │   ├── style.css          # Global styles
│   │   │   └── admin.css          # Admin panel styles
│   │   ├── js/
│   │   │   ├── main.js            # Global JS
│   │   │   └── admin.js           # Admin panel JS
│   │   ├── img/                   # Images
│   │   └── fonts/                 # Web fonts
│   │
│   └── src/                       # HTML source pages
│       ├── pages/                 # Public-facing pages
│       │   ├── index.html
│       │   ├── programs.html
│       │   ├── gallery.html
│       │   ├── blog.html
│       │   └── contact.html
│       │
│       └── admin/                 # Admin panel pages (protected)
│           ├── login.html
│           ├── dashboard.html
│           ├── users.html
│           └── submissions.html
│
├── server/                        # Backend (PHP)
│   ├── controllers/               # Business logic
│   │   ├── AuthController.php
│   │   ├── AdminController.php
│   │   └── ContactController.php
│   │
│   ├── routes/                    # HTTP endpoints
│   │   ├── auth.php               # POST /server/routes/auth.php
│   │   ├── contact.php            # POST /server/routes/contact.php
│   │   ├── users.php              # GET|DELETE /server/routes/users.php
│   │   └── submissions.php        # GET|DELETE /server/routes/submissions.php
│   │
│   ├── models/                    # Database models (PDO)
│   │   ├── User.php
│   │   └── Submission.php
│   │
│   ├── middleware/                # Guards & request filters
│   │   ├── auth.php               # requireAdmin() / requireAuth()
│   │   └── cors.php               # CORS headers
│   │
│   ├── config/
│   │   └── database.php           # PDO singleton (getDB())
│   │
│   ├── utils/
│   │   └── helpers.php            # sanitize(), jsonResponse(), loadEnv()
│   │
│   └── server.php                 # Backend entry / router
│
├── database/
│   ├── migrations/
│   │   ├── 001_create_users_table.sql
│   │   └── 002_create_submissions_table.sql
│   │
│   └── seeders/
│       └── seed_admin_user.sql    # Default admin account
│
├── assets/                        # Legacy / third-party assets (Bootstrap, etc.)
├── .env                           # Environment variables (not committed)
├── .htaccess                      # Apache rewrite rules
└── index.php                      # XAMPP root entry point
```

---

## Getting Started (XAMPP)

### 1. Clone / place files
Put this folder inside `C:\xampp\htdocs\`.

### 2. Create the database
Open **phpMyAdmin** (http://localhost/phpmyadmin), create a database named `glo_ced_india`, then run:

```sql
-- Run in order:
source database/migrations/001_create_users_table.sql;
source database/migrations/002_create_submissions_table.sql;
source database/seeders/seed_admin_user.sql;
```

### 3. Configure .env
Edit `.env` with your database credentials:

```
DB_HOST=localhost
DB_NAME=glo_ced_india
DB_USER=root
DB_PASSWORD=
```

### 4. Start Apache & MySQL in XAMPP

### 5. Open in browser
- **Frontend:** http://localhost/Glo-CED_India_Webpage/client/src/pages/index.html
- **Admin Login:** http://localhost/Glo-CED_India_Webpage/client/src/admin/login.html

Default admin credentials:
- Username: `admin`
- Password: `admin123`  ← **change this immediately**

---

## Tech Stack

| Layer    | Technology                  |
|----------|-----------------------------|
| Frontend | HTML5, CSS3, Vanilla JS     |
| Backend  | PHP 8+                      |
| Database | MySQL (via PDO)             |
| Server   | Apache (XAMPP)              |
