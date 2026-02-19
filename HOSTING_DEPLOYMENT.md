# Hosting & Deployment Guide - Glo-CED India Website

This guide covers how to properly deploy the Glo-CED India website to production.

## Overview

The website is configured to work in two environments:
- **Local Development**: `http://localhost/STP/`
- **Production**: `https://yourdomain.com/` (or any subdirectory)

## Key Configuration Files

### 1. `.htaccess` (Root Directory)
Handles URL routing and redirects. The `RewriteBase` is set to `/STP/` by default.

**For production deployment to domain root:**
```apache
RewriteBase /
```

**For subdirectory deployment (e.g., `/charity/`):**
```apache
RewriteBase /charity/
```

### 2. `config/paths.php`
Dynamically determines the correct base path based on the request URI. Works automatically for both `/STP/` and root deployments.

### 3. `assets/js/path-config.js`
Frontend JavaScript configuration that automatically detects the correct base path for API calls. No manual updates needed.

## Deployment Steps

### Option 1: Deploy to Domain Root

1. **Update `.htaccess`**
   - Change `RewriteBase /STP/` to `RewriteBase /`
   - Keep all other rules the same

2. **Upload files** to your hosting root directory
   - All files from `/STP/` directory go to root

3. **Database setup**
   - Update `config/db_config.php` with production database credentials
   - Run `config/database_schema.sql` to create tables

4. **File permissions**
   ```
   chmod 755 /backend/
   chmod 755 /admin/
   chmod 600 config/db_config.php (make config file readable by PHP only)
   ```

### Option 2: Deploy to Subdirectory

1. **Upload files** to `/charity/` (or your chosen subdirectory)

2. **Update `.htaccess`**
   - Change `RewriteBase /STP/` to `RewriteBase /charity/`

3. **Follow database setup steps** from Option 1

4. **The frontend dynamically detects** the path, so no JavaScript updates needed

### Option 3: Keep as `/STP/` (Recommended for Development)

No changes needed. The site already works as-is.

## Important Security Steps

1. **Protect sensitive files** - Already configured in `.htaccess`:
   ```apache
   <FilesMatch "(\.env|\.htaccess|\.git|db_config\.php)">
       Deny from all
   </FilesMatch>
   ```

2. **Update database credentials** in `config/db_config.php`
   ```php
   define('DB_HOST', 'your-host');
   define('DB_USER', 'your-user');
   define('DB_PASS', 'your-password');
   define('DB_NAME', 'your-database');
   ```

3. **Set proper file permissions**
   ```bash
   chmod 644 *.html *.php
   chmod 755 assets/ backend/ admin/ config/
   chmod 600 config/db_config.php
   ```

4. **HTTPS** - Always use HTTPS in production
   - Add to `.htaccess` to force HTTPS:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

## API Endpoints (After Deployment)

### Contact Form
- **Endpoint**: `/backend/contact_process.php`
- **Method**: POST
- **Automatically handled** by `path-config.js`

### Admin Panel
- **Access**: `/admin/` or `/admin/admin_users.php`
- **Note**: Ensure admin folder is protected with authentication

## Environment Detection

The site **automatically detects** the correct paths:

```javascript
// path-config.js handles this automatically
window.APP_CONFIG = {
    basePath: '/STP',  // or '/' in production
    apiUrl: '/STP/backend',
    assetsUrl: '/STP/assets',
    pagesUrl: '/STP/pages'
}
```

No manual path updates in HTML/JavaScript needed!

## Testing Before Deployment

1. **Test locally first**
   ```
   http://localhost/STP/
   http://localhost/STP/contact_new.html
   ```

2. **Test contact form**
   - Fill out and submit
   - Check browser console for any errors
   - Verify success/error messages

3. **Test navigation**
   - All internal links should work
   - Check mobile menu

4. **Test forms and API calls**
   - Verify database connections
   - Check backend logs

## Troubleshooting

### 404 Error on Contact Form
- Check `.htaccess` RewriteBase path
- Verify `backend/contact_process.php` exists
- Check browser console for exact error

### Assets not loading
- Verify `assets/` directory exists
- Check `.htaccess` doesn't block asset access
- Ensure `path-config.js` is loaded first

### Relative paths not working
- Ensure `.htaccess` is present and enabled
- Check Apache `mod_rewrite` is installed
- Verify `RewriteBase` is correct

## Additional Resources

- **Database Schema**: `config/database_schema.sql`
- **Configuration**: `config/db_config.php`
- **Path Helper**: `config/paths.php` (for PHP files)
- **Frontend Helper**: `assets/js/path-config.js` (for HTML/JS)

## Support

For issues:
1. Check browser console (F12) for JavaScript errors
2. Check server logs in `/var/log/apache2/` or hosting control panel
3. Verify database connection in `config/db_config.php`
4. Test individual files to isolate issues

---
**Last Updated**: February 2026
**Compatible with**: Apache 2.4+, PHP 7.0+, MySQL 5.7+
