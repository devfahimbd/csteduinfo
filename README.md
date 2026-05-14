<div align="center">

<img src="https://raw.githubusercontent.com/cst-kpi/csteduinfo/main/assets/images/readme-banner.png" alt="CST Department Website Banner" width="100%"/>

<h1>CST Department Website</h1>

<p><strong>Computer Science & Technology Department, Khulna Polytechnic Institute</strong></p>

<p>A complete, professional, and responsive educational department website built with core web technologies. Features a public-facing frontend with an integrated admin control panel, student result management system, and dynamic content management capabilities — all powered by pure PHP and MySQL without any external frameworks.</p>

<p>
  <a href="#features">Features</a> &bull;
  <a href="#tech-stack">Tech Stack</a> &bull;
  <a href="#project-structure">Structure</a> &bull;
  <a href="#database-schema">Database</a> &bull;
  <a href="#setup--deployment">Setup</a> &bull;
  <a href="#api-endpoints">API</a> &bull;
  <a href="#security">Security</a> &bull;
  <a href="#license">License</a>
</p>

</div>

---

## Screenshots / Preview

| Homepage | Admin Dashboard |
|:---:|:---:|
| ![Homepage](https://raw.githubusercontent.com/cst-kpi/csteduinfo/main/assets/images/readme-homepage.png) | ![Dashboard](https://raw.githubusercontent.com/cst-kpi/csteduinfo/main/assets/images/readme-dashboard.png) |

> Replace the placeholder images above with actual screenshots of your website and admin panel.

---

## Features

### Public Frontend
- **Responsive Design** — Fully responsive layout that works on desktop, tablet, and mobile devices
- **Bangla Language Support** — Entire UI rendered in Bengali with Google Fonts (Hind Siliguri)
- **Dynamic Homepage** — Hero section with Lottie animation, statistics counter, featured notices, teachers, and gallery
- **Semester Roadmap** — Interactive 8-semester academic journey with mind-map style SVG connections
- **Notice Board** — Categorized notices (General, Academic, Event, Exam) with pagination and detail pages
- **Faculty Page** — Teacher profiles with designation, qualification, social links, and photo gallery
- **Photo Gallery** — Category-based gallery with lightbox viewer, filter tabs, and navigation
- **Resource Library** — Downloadable study materials, syllabus, previous questions, and class routines
- **Result System** — BTEB student result search by roll number with detailed subject-wise results
- **Contact Form** — CSRF-protected contact form with email, phone, and address information
- **SEO Optimized** — Open Graph meta tags, Twitter cards, canonical URLs, XML sitemap, and robots.txt
- **Loading Animation** — Lottie-powered loading overlay with fallback support
- **Scroll to Top** — Floating button with smooth scroll behavior
- **404 Error Page** — Custom error page with Lottie animation

### Admin Control Panel
- **Dashboard** — Real-time statistics overview with content summary widgets
- **Notice Management** — Full CRUD operations with categories, image upload, and rich content
- **Faculty Management** — Teacher profiles with photo upload, designation, qualification, and social links
- **Gallery Management** — Photo upload with categories and image optimization
- **Resource Management** — File upload system for PDFs, documents, and external links
- **Category Management** — Unified category system for notices, teachers, gallery, and resources
- **Sponsor Management** — Logo upload with sorting and external link support
- **Credits & Acknowledgements** — Team member profiles with photos, roles, and social links
- **Message Inbox** — View and manage contact form submissions with read/unread status
- **Site Settings** — Dynamic configuration for site name, logo, contact info, and social media URLs
- **Result System** — Bulk JSON upload with chunked processing, progress bar, and validation (supports 55K+ records)
- **Subject Code Manager** — BTEB subject code to name mapping with Theory/Practical full names
- **Result Data Viewer** — Browse, search, and analyze uploaded result data with college-wise stats
- **Authentication** — Session-based login with CSRF protection

---

## Tech Stack

<img src="https://raw.githubusercontent.com/cst-kpi/csteduinfo/main/assets/images/readme-techstack.png" alt="Tech Stack" width="100%"/>

### Backend
| Technology | Version | Purpose |
|:---|:---|:---|
| ![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white) | 7.4+ | Server-side scripting, business logic |
| ![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white) | 5.7+ | Relational database, data storage |
| **PDO (PHP Data Objects)** | Built-in | Database abstraction layer, prepared statements |
| **Apache** (with mod_rewrite) | 2.4+ | Web server, URL rewriting |

### Frontend
| Technology | Version | Purpose |
|:---|:---|:---|
| ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) | 5 | Page structure, semantic markup |
| ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) | 3 | Custom styling, animations, responsive layout |
| ![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=flat-square&logo=javascript&logoColor=black) | ES6+ | Client-side interactivity, DOM manipulation |

### External Libraries / CDN Dependencies
| Library | Version | Source | Purpose |
|:---|:---|:---|:---|
| **Hind Siliguri** | wght@300-700 | Google Fonts | Bengali (Bangla) typography for the public frontend |
| **Inter** | wght@400-700 | Google Fonts | Clean UI typography for the admin panel |
| **@lottiefiles/lottie-player** | latest | unpkg CDN | Lightweight Lottie animation player for loading screens and hero sections |

### CSS Libraries Used
> This project uses **NO external CSS frameworks** (no Bootstrap, no Tailwind, no Foundation). All styling is built from scratch with custom CSS.

| File | Lines | Description |
|:---|:---:|:---|
| `assets/css/style.css` | 1600+ | Complete frontend stylesheet — reset, layout, components, animations, responsive breakpoints |
| `assets/css/admin.css` | 917 | Admin panel stylesheet — sidebar layout, data tables, forms, dashboard components |

### JavaScript Libraries Used
> This project uses **NO external JavaScript libraries** (no jQuery, no React, no Vue). All interactivity is vanilla JavaScript.

| File | Lines | Description |
|:---|:---:|:---|
| `assets/js/main.js` | 395 | Sticky header, mobile menu toggle, smooth scroll, gallery filter, semester mind-map animation, dropdown menu, contact form validation |

### Icons
> This project uses **NO icon libraries** (no FontAwesome, no Material Icons). All icons are **custom inline SVGs** inspired by the Feather Icons design system, rendered through a PHP `icon()` helper function that supports 40+ icon types.

---

## Architecture

<img src="https://raw.githubusercontent.com/cst-kpi/csteduinfo/main/assets/images/readme-architecture.png" alt="Architecture Diagram" width="100%"/>

```
┌──────────────────────────────────────────────────────────┐
│                    CLIENT (Browser)                       │
│  HTML + Custom CSS + Vanilla JS + Lottie Animations       │
│  Google Fonts (Hind Siliguri / Inter)                     │
└──────────────────────┬───────────────────────────────────┘
                       │ HTTP Requests
                       ▼
┌──────────────────────────────────────────────────────────┐
│                 APACHE WEB SERVER                         │
│  mod_rewrite (URL Rewriting) + .htaccess                  │
│  PHP 7.4+ (Core Application Logic)                        │
│  ├─ Public Pages (index.php, about.php, etc.)             │
│  ├─ API Routes (api/*.php)                                │
│  └─ Admin Panel (control-panel/*.php)                     │
└──────────────────────┬───────────────────────────────────┘
                       │ PDO (Prepared Statements)
                       ▼
┌──────────────────────────────────────────────────────────┐
│                    MYSQL DATABASE                          │
│  Database: cst_department                                  │
│  13 Tables (admins, settings, notices, teachers, etc.)     │
│  Engine: InnoDB | Charset: utf8mb4                          │
└──────────────────────────────────────────────────────────┘
```

---

## Project Structure

```
csteduinfo/
│
├── index.php                    # Homepage — hero, stats, notices, teachers, gallery, semester roadmap
├── about.php                    # About page — department info, mission & vision
├── faculty.php                  # Faculty listing — teacher cards with filter
├── teacher-details.php          # Single teacher profile page
├── notice.php                   # Notice listing with category filter & pagination
├── notice-details.php           # Single notice detail page
├── gallery.php                  # Photo gallery with category filter & lightbox
├── gallery-details.php          # Single gallery image detail page
├── resources.php                # Resource listing — study materials, syllabus, etc.
├── resource-details.php         # Single resource detail page
├── result.php                   # Student result search (by roll number)
├── contact.php                  # Contact page with form
├── download.php                 # File download handler (PDFs, docs)
├── 404.php                      # Custom 404 error page
├── sitemap.php                  # Dynamic XML sitemap generator
├── robots.txt                   # Search engine crawl rules
│
├── api/                         # RESTful API Endpoints
│   ├── upload-bulk.php          #   Chunked bulk result upload (2000 records/chunk)
│   ├── upload-json.php          #   Single JSON result upload
│   ├── result-search.php        #   Student result search by roll
│   └── result-stats.php         #   Result statistics API
│
├── includes/                    # Shared PHP Components
│   ├── config.php               #   Database config, constants, helper functions, session management
│   ├── functions.php            #   CSRF, sanitization, pagination, SEO, icons, file upload, slug generator
│   ├── header.php               #   Public page header (navbar, meta tags, loading overlay)
│   ├── footer.php               #   Public page footer (CTA, sponsors, credits, links, lightbox, scroll-to-top)
│   ├── admin-sidebar.php        #   Admin panel sidebar navigation (shared component)
│   └── result-parser.php        #   Result data manager class (JSON import, chunk upload, batch management)
│
├── control-panel/               # Admin Control Panel
│   ├── index.php                #   Admin dashboard (stats overview, recent content)
│   ├── login.php                #   Admin login page
│   ├── logout.php               #   Admin logout handler
│   ├── auth-check.php           #   Session authentication middleware
│   │
│   ├── dashboard.php            #   Dashboard widgets and quick actions
│   │
│   ├── notices.php              #   Notices listing with search & pagination
│   ├── notices/
│   │   ├── index.php            #   (alias) Notices list
│   │   ├── create.php           #   Create new notice
│   │   ├── edit.php             #   Edit existing notice
│   │   └── delete.php           #   Delete notice (POST confirmation)
│   ├── notice-edit.php          #   Add/edit notice form
│   │
│   ├── teachers.php             #   Faculty listing
│   ├── teachers/
│   │   ├── index.php            #   (alias) Teachers list
│   │   ├── create.php           #   Add new teacher
│   │   ├── edit.php             #   Edit teacher profile
│   │   └── delete.php           #   Delete teacher
│   ├── teacher-edit.php         #   Add/edit teacher form
│   │
│   ├── gallery.php              #   Gallery management
│   ├── gallery/
│   │   ├── index.php            #   (alias) Gallery list
│   │   └── delete.php           #   Delete gallery image
│   ├── gallery-edit.php         #   Add/edit gallery image
│   │
│   ├── resources.php            #   Resource listing
│   ├── resources/
│   │   ├── index.php            #   (alias) Resources list
│   │   ├── create.php           #   Add new resource
│   │   ├── edit.php             #   Edit resource
│   │   └── delete.php           #   Delete resource
│   ├── resource-edit.php        #   Add/edit resource form
│   │
│   ├── categories.php           #   Category management
│   ├── categories/
│   │   ├── index.php            #   (alias) Categories list
│   │   ├── create.php           #   Add category
│   │   ├── edit.php             #   Edit category
│   │   └── delete.php           #   Delete category
│   │
│   ├── sponsors.php             #   Sponsor management
│   ├── sponsors/
│   │   ├── index.php            #   (alias) Sponsors list
│   │   ├── create.php           #   Add sponsor
│   │   └── delete.php           #   Delete sponsor
│   │
│   ├── credits.php              #   Credits & acknowledgements management
│   ├── credits/
│   │   ├── index.php            #   (alias) Credits list
│   │   ├── create.php           #   Add credit entry
│   │   └── delete.php           #   Delete credit
│   │
│   ├── messages.php             #   Contact message inbox
│   ├── settings.php             #   Site settings (name, logo, contact, social links)
│   │
│   ├── result-json-upload.php   #   Bulk result JSON upload with progress bar
│   ├── result-data.php          #   View and search uploaded result data
│   ├── result-subjects.php      #   BTEB subject code manager
│   └── result-scraper.php       #   Result scraping utility
│
├── assets/                      # Static Assets
│   ├── css/
│   │   ├── style.css            #   Frontend stylesheet (custom, no framework)
│   │   └── admin.css            #   Admin panel stylesheet (custom, no framework)
│   ├── js/
│   │   └── main.js              #   Frontend JavaScript (vanilla, no libraries)
│   ├── lottie/
│   │   ├── loading.json         #   Loading spinner animation
│   │   ├── coding.json          #   Hero section coding animation
│   │   ├── developer.json       #   Developer illustration
│   │   ├── not-found.json       #   404 page animation
│   │   ├── error-404.json       #   Error 404 animation (alt)
│   │   ├── not-found.lottie     #   Lottie binary format
│   │   └── error-404.lottie     #   Lottie binary format (alt)
│   ├── images/
│   │   └── .gitkeep             #   Placeholder for project images
│   └── uploads/                 # User-uploaded files (dynamic)
│       ├── notices/             #   Notice attachment images
│       ├── resources/           #   Resource files (PDF, DOC, etc.)
│       ├── gallery/             #   Gallery images
│       ├── teachers/            #   Teacher profile photos
│       ├── sponsors/            #   Sponsor logos
│       ├── logo/                #   Site logo
│       └── favicon/             #   Site favicon
│
├── database/                    # Database Schema & Migrations
│   ├── schema.sql               #   Complete database schema (13 tables + seed data)
│   ├── result_tables.sql        #   Result system tables (batches, subjects, students)
│   └── migration_add_file_name.sql  # Migration: add file_name column to resources
│
├── sample-result.json           # Sample BTEB result JSON (for testing upload)
├── SETUP-GUIDE.txt              # Bulk upload system setup guide
├── DEPLOYMENT.txt               # Detailed deployment instructions
├── LICENSE                      # MIT License
└── README.md                    # This file
```

---

## Database Schema

**Database:** `cst_department` &bull; **Engine:** InnoDB &bull; **Charset:** utf8mb4_unicode_ci

### Core Tables (9)

| Table | Purpose | Key Columns |
|:---|:---|:---|
| `admins` | Admin user accounts | id, name, email, password (bcrypt) |
| `settings` | Dynamic site configuration | setting_key, setting_value |
| `categories` | Unified categories for all content types | name, slug, type (notice/teacher/gallery/resource) |
| `notices` | Notice board entries | title, slug, content, category_id, image, is_important, status |
| `teachers` | Faculty member profiles | name, designation, qualification, email, phone, bio, category_id, image, social links |
| `gallery` | Photo gallery images | title, slug, description, category_id, image, status |
| `resources` | Downloadable study materials | title, slug, description, category_id, file_path, file_name, external_url |
| `sponsors` | Sponsor/partner logos | name, website, logo, sort_order, status |
| `credits` | Team credits & acknowledgements | name, role, about, image, social links, section, sort_order |
| `contact_messages` | Contact form submissions | name, email, subject, message, is_read, created_at |

### Result System Tables (3)

| Table | Purpose | Key Columns |
|:---|:---|:---|
| `result_batches` | Batch metadata for each result upload | exam_year, regulation_year, semester, program, total_students, total_passed, total_failed, status |
| `result_subjects` | BTEB subject code to name mapping | subject_code, subject_name, t_full_name, p_full_name |
| `result_students` | Individual student results | batch_id, roll, college_code, college_name, gpa, result_type, failed_subjects_json |

### Relationships
```
categories ──┬──> notices (category_id)
             ├──> teachers (category_id)
             ├──> gallery (category_id)
             └──> resources (category_id)

result_batches ──> result_students (batch_id, CASCADE DELETE)
```

### Default Admin Credentials
| Field | Value |
|:---|:---|
| Email | `admin@cst.edu.bd` |
| Password | `admin123` |

> **Important:** Change the default password immediately after first login in production.

---

## Setup & Deployment

### System Requirements

| Requirement | Minimum Version |
|:---|:---|
| PHP | 7.4+ (8.0+ recommended) |
| MySQL | 5.7+ (8.0+ recommended) |
| Apache | 2.4+ with mod_rewrite enabled |
| PHP Extensions | PDO, PDO_mysql, mbstring, json, session |
| Web Browser | Chrome, Firefox, Edge (latest) |

### Local Development Setup (XAMPP)

**Step 1 — Copy Files**
```bash
# Copy the entire project folder to your XAMPP htdocs directory
cp -r csteduinfo/ /Applications/XAMPP/xamppfiles/htdocs/csteduinfo/
# On Windows: Extract to C:\xampp\htdocs\csteduinfo\
```

**Step 2 — Start XAMPP**
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

**Step 3 — Import Database**
1. Open browser: `http://localhost/phpmyadmin`
2. Click **New** to create database
3. Name: `cst_department`, Collation: `utf8mb4_unicode_ci`
4. Go to **Import** tab
5. Select `database/schema.sql`
6. Click **Import**
7. Repeat for `database/result_tables.sql` (if you need the result system)

**Step 4 — Configure Database Connection**
```php
// Edit includes/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cst_department');
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASS', '');           // Your MySQL password
```

**Step 5 — Set File Permissions**
```bash
# Make upload directories writable
chmod -R 755 assets/uploads/
chmod -R 777 assets/uploads/notices/
chmod -R 777 assets/uploads/resources/
chmod -R 777 assets/uploads/gallery/
chmod -R 777 assets/uploads/teachers/
chmod -R 777 assets/uploads/sponsors/
chmod -R 777 assets/uploads/logo/
chmod -R 777 assets/uploads/favicon/
```

**Step 6 — Access the Website**
```
Frontend:      http://localhost/csteduinfo/
Admin Panel:   http://localhost/csteduinfo/control-panel/
Admin Login:   admin@cst.edu.bd / admin123
```

### Production Deployment

**1. Upload Files**
```bash
# Upload to your web server's document root
scp -r csteduinfo/ user@server:/var/www/html/csteduinfo/
```

**2. Create Database**
```bash
mysql -u root -p -e "CREATE DATABASE cst_department CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p cst_department < database/schema.sql
mysql -u root -p cst_department < database/result_tables.sql
```

**3. Update Configuration**
```php
// includes/config.php — Update for production
define('DB_USER', 'your_production_db_user');
define('DB_PASS', 'your_strong_password');

// Disable error display (already set in config.php)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

**4. Secure the Installation**
- Change the default admin password immediately
- Restrict access to `/control-panel/` via server authentication
- Enable HTTPS/SSL certificate
- Set proper file permissions (644 for files, 755 for directories)
- Block direct access to `/assets/uploads/` directory

**5. Apache Configuration (Optional)**
```apache
# .htaccess — Increase PHP limits for bulk upload
php_value upload_max_filesize 200M
php_value post_max_size 200M
php_value max_execution_time 3600
php_value max_input_time 3600
php_value memory_limit 512M
```

**6. Enable mod_rewrite**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## API Endpoints

All API endpoints are located in the `/api/` directory.

### `POST /api/upload-json.php`
Upload result data from a single JSON file.

**Request Body:** `multipart/form-data`
| Parameter | Type | Description |
|:---|:---|:---|
| `json_file` | File | JSON file with student result data |
| `exam_year` | String | Examination year |
| `regulation_year` | String | Regulation year |
| `semester` | String | Semester name (e.g., "1st Semester") |
| `csrf_token` | String | CSRF token for security |

### `POST /api/upload-bulk.php`
Chunked bulk upload for large datasets (55K+ students).

**Actions:**
| Action | Description |
|:---|:---|
| `init` | Create empty batch record |
| `chunk` | Upload a chunk of 2000 student records |
| `finish` | Finalize batch and calculate statistics |

### `GET /api/result-search.php`
Search student results by roll number.

**Query Parameters:**
| Parameter | Type | Description |
|:---|:---|:---|
| `roll` | String | Student roll number |

### `GET /api/result-stats.php`
Get result statistics for a batch.

**Query Parameters:**
| Parameter | Type | Description |
|:---|:---|:---|
| `batch_id` | Integer | Result batch ID |

### Supported JSON Formats for Result Upload

```json
// Format 1: Object with roll as key
{
  "200010": { "roll": "200010", "cgpa": 3.52, "status": "passed" }
}

// Format 2: Array of student objects
[
  { "roll": "200010", "cgpa": 3.52, "status": "passed" }
]

// Format 3: Wrapped with "students" array
{
  "students": [{ "roll": "200010", "cgpa": 3.52, "status": "passed" }]
}

// Format 4: Complete JSON (auto-detects exam info)
{
  "exam_year": "2022",
  "regulation_year": "2016",
  "semester": "1st Semester",
  "students": [{ "roll": "200010", "cgpa": 3.52, "status": "passed" }]
}
```

---

## Security Features

| Feature | Implementation |
|:---|:---|
| **CSRF Protection** | Token-based CSRF validation on all forms (`generateCSRFToken()` / `verifyCSRFToken()`) |
| **SQL Injection Prevention** | All database queries use PDO prepared statements with parameterized queries |
| **XSS Prevention** | Output sanitization via `htmlspecialchars()` with `ENT_QUOTES` and `UTF-8` encoding |
| **Input Sanitization** | `sanitize()` and `sanitizeInput()` functions for all user inputs |
| **Authentication** | Session-based admin authentication with `requireLogin()` middleware |
| **File Upload Validation** | Extension whitelist, file size limits (50MB max), unique filename generation |
| **Error Handling** | Production mode disables error display, logs errors to server log |
| **Session Security** | Secure session configuration with proper session start handling |
| **Admin Area Protection** | `auth-check.php` middleware guards all admin panel pages |
| **robots.txt** | Blocks search engines from indexing `/control-panel/` and `/assets/uploads/` |

---

## File Information Reference

### Which Config Info is in Which File

| Information | File |
|:---|:---|
| Database credentials | `includes/config.php` |
| Site URL (auto-detected) | `includes/config.php` |
| Upload paths & limits | `includes/config.php` |
| Session configuration | `includes/config.php` |
| Site settings (dynamic) | Stored in `settings` database table |
| Navigation menu items | `includes/header.php` |
| Footer links & layout | `includes/footer.php` |
| Admin sidebar menu | `includes/admin-sidebar.php` |
| SVG icon definitions | `includes/functions.php` (`icon()` function) |
| CSS custom properties | `assets/css/style.css` (`:root` variables) |
| Admin color scheme | `assets/css/admin.css` (`:root` variables) |

### Which Files are Required vs Optional

**Required (Core Application):**
- `includes/config.php` — Application will not run without this
- `includes/functions.php` — All helper functions
- `includes/header.php` — Required for all public pages
- `includes/footer.php` — Required for all public pages
- `assets/css/style.css` — All frontend styling
- `assets/js/main.js` — Frontend interactivity
- `database/schema.sql` — Required database structure

**Required (Admin Panel):**
- `control-panel/login.php` — Admin authentication
- `control-panel/auth-check.php` — Security middleware
- `control-panel/index.php` — Admin dashboard
- `assets/css/admin.css` — Admin panel styling
- `includes/admin-sidebar.php` — Admin navigation

**Optional / Feature-Specific:**
- `api/*.php` — Only needed if using the result API
- `database/result_tables.sql` — Only needed if using the result system
- `includes/result-parser.php` — Only needed for result processing
- `database/migration_add_file_name.sql` — One-time migration for existing installations
- `sample-result.json` — Testing file, not needed in production
- `SETUP-GUIDE.txt` — Documentation only
- `DEPLOYMENT.txt` — Documentation only
- `assets/lottie/*.json` — Fallback to static display if missing

---

## Credits & Development

<div align="center">

**Developed by**

**MD Fahim Sheikh**

<p>Built with core web technologies. No frameworks, no dependencies, no build tools.</p>

<p>
  <strong>Pixel Programmers</strong> &bull;
  <strong>Eternity Global Innovation</strong> &bull;
  <strong>Pixel Motion X</strong>
</p>

</div>

---

## License

This project is licensed under the **MIT License**.

```
MIT License

Copyright (c) 2026 MD Fahim Sheikh

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

<div align="center">

<p><sub>Built with core PHP, MySQL, CSS & JavaScript — Zero Frameworks, Zero Dependencies</sub></p>

</div>
