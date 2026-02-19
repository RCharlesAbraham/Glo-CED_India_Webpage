# Quick Deployment Reference

## Current Setup
- **Local**: `http://localhost/STP/`
- **Status**: ✅ Ready to use

## To Deploy Elsewhere

### 1️⃣ Update `.htaccess` RewriteBase

**Current** (`/STP/`):
```apache
RewriteBase /STP/
```

**For domain root example.com**:
```apache
RewriteBase /
```

**For subdirectory example.com/charity**:
```apache
RewriteBase /charity/
```

---

### 2️⃣ Update Database Config

Edit `config/db_config.php`:
```php
define('DB_HOST', 'your-db-host.com');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');
define('DB_NAME', 'your-database-name');
```

---

### 3️⃣ Upload Files

- Copy all files from `/STP/` to your hosting
- Keep folder structure same
- Ensure `.htaccess` is uploaded (hidden file)

---

### 4️⃣ Set File Permissions

```bash
# Make directories writable
chmod 755 backend/ admin/ assets/

# Make config file read-only by PHP
chmod 600 config/db_config.php

# Make HTML/PHP files readable
chmod 644 *.html *.php backend/*.php
```

---

### 5️⃣ Force HTTPS (Production)

Add to root `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Environment Auto-Detection

✨ **No additional setup needed!**

The website automatically detects paths via:
- `config/paths.php` (PHP backend)
- `assets/js/path-config.js` (Frontend)

---

## Testing Checklist

- [ ] Homepage loads: `domain.com/`
- [ ] Navigation works: `domain.com/programs.html`
- [ ] Contact form loads: `domain.com/contact_new.html`
- [ ] Contact form submits successfully
- [ ] All assets load (CSS, JS, images)
- [ ] Mobile menu works
- [ ] No 404 errors in console

---

## File Structure

```
STP/
├── .htaccess                    (Main routing)
├── index.php                    (Entry point)
├── pages/
│   ├── index.html               (Homepage)
│   ├── contact_new.html         (Contact form)
│   ├── programs.html
│   └── ...
├── backend/
│   ├── .htaccess               (API protection)
│   ├── contact_process.php     (Form handler)
│   └── ...
├── config/
│   ├── paths.php               (Path detection)
│   ├── db_config.php           (Database config)
│   └── database_schema.sql
├── assets/
│   ├── js/
│   │   ├── path-config.js      (Frontend path detection)
│   │   └── ...
│   ├── css/
│   └── img/
└── admin/
    └── ... (admin panel)
```

---

## Common Issues & Fixes

### Issue: 404 on contact form
**Fix**: Check `.htaccess` RewriteBase matches your deployment path

### Issue: Assets not loading
**Fix**: Verify `assets/` folder exists and has correct permissions

### Issue: Database connection fails
**Fix**: Update credentials in `config/db_config.php`

### Issue: Forms not working
**Fix**: Check `backend/contact_process.php` exists and is accessible

---

## FAQ

**Q: Do I need to update HTML files when changing deployment path?**
A: No! Path detection is automatic.

**Q: How do I move from localhost to production?**
A: Just update `.htaccess` RewriteBase and upload files.

**Q: Are sensitive files protected?**
A: Yes, `.htaccess` blocks access to `.env`, `db_config.php`, etc.

**Q: What if I'm on shared hosting without mod_rewrite?**
A: Contact host support to enable Apache mod_rewrite.

---

## Support Resources

- **Hosting Guide**: `HOSTING_DEPLOYMENT.md`
- **Database**: `config/database_schema.sql`
- **Config**: `config/db_config.php`
- **Path Helper**: `config/paths.php` or `assets/js/path-config.js`
