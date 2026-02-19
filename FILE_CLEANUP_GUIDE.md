# File Cleanup & Organization

## Redundant Files (Safe to Remove)

These files are legacy/backups and are NOT used in production:

```
❌ /program.html                 → OLD Bootstrap template (not used)
❌ /latest_root.html             → Backup of homepage design
❌ /programs.html                → Redirect via .htaccess to /pages/programs.html
```

### Why They're Redundant:
- `.htaccess` automatically routes `programs.html` → `pages/programs.html`
- Real active pages are in `/pages/` folder with Tailwind CSS design
- Old Bootstrap template (`program.html`) is not referenced anywhere

## Current Production Files (in `/pages/`)

```
✅ pages/index.html              → Homepage
✅ pages/programs.html           → "What We Do" page
✅ pages/contact_new.html        → Contact form
✅ pages/gallery.html            → Gallery
✅ pages/blog.html               → Blog (if used)
✅ pages/elements.html           → Elements page
```

## Routing Flow

```
User Request          .htaccess Rule                  Served File
─────────────────────────────────────────────────────────────────
/                  → pages/index.html
/programs.html     → pages/programs.html
/programs          → pages/programs.html
/contact_new.html  → pages/contact_new.html
/gallery.html      → pages/gallery.html
```

## Cleanup Options

### Option 1: Move to Archive (Safest)
```bash
mkdir archive/
mv program.html archive/
mv latest_root.html archive/
mv programs.html archive/
```

### Option 2: Delete (Permanent)
```bash
rm program.html
rm latest_root.html
rm programs.html
```

### Option 3: Keep But Document
Add a comment in each file indicating it's archived.

---

## File Status Summary

| File | Location | Framework | Status | Action |
|------|----------|-----------|--------|--------|
| index.php | /STP/ | PHP/Router | ✅ Active | Keep (simplified) |
| index.html | /pages/ | Tailwind | ✅ Active | Keep |
| programs.html | /pages/ | Tailwind | ✅ Active | Keep |
| contact_new.html | /pages/ | Tailwind | ✅ Active | Keep |
| program.html | /STP/ | Bootstrap | ❌ Obsolete | Remove |
| programs.html | /STP/ | Tailwind | ❌ Obsolete | Remove |
| latest_root.html | /STP/ | Tailwind | ❌ Backup | Remove |

---

### Recommended: Remove These Files

The root-level `program.html`, `programs.html`, and `latest_root.html` serve no purpose with the `.htaccess` routing in place. The real content is in `/pages/`.

Would you like me to delete them?
