# USeP Vehicle Reservation & Dispatch Management System

> A web-based reservation and dispatch management system for university vehicles.  
> Built with **PHP 8.1+**, **MySQL 8.0+**, **HTML5**, **CSS3**, **JavaScript (ES6+)**, and **AJAX**.

---

## Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Technology Stack](#technology-stack)
4. [Architecture (MVC)](#architecture-mvc)
5. [Project Structure](#project-structure)
6. [Database Schema (ERD)](#database-schema-erd)
7. [Reservation Workflow](#reservation-workflow)
8. [AJAX Live Update System](#ajax-live-update-system)
9. [Real-Time Notification System](#real-time-notification-system)
10. [Security](#security)
11. [Installation](#installation)
12. [Test Credentials](#test-credentials)

---

## Overview

The **USeP Vehicle Reservation System (VRS)** enables university personnel to request, approve, dispatch, and track official vehicle usage. It implements a **multi-level approval workflow** (Requester → Unit Head → ASD Coordinator → Driver) with role-based access control.

---

## Features

| Feature | Description |
|---|---|
| **Role-Based Access** | 5 roles: Admin, ASD Coordinator, Unit Head, Requester, Driver |
| **Multi-Level Approval** | Requests flow through Unit Head → ASD Coordinator with remarks |
| **Vehicle & Driver Assignment** | ASD Coordinator assigns both vehicle and driver upon final approval |
| **Dispatch Tracking** | Drivers log start/end mileage, fuel consumption, and trip notes |
| **AJAX Live Updates** | All form submissions use AJAX — no page reloads |
| **Toast Notifications** | Real-time success/error/warning messages without page reload |
| **Fleet Management** | CRUD operations for vehicles with status tracking |
| **Account Management** | Admin manages user accounts with activate/deactivate toggle |
| **Reports** | Daily, vehicle utilization, monthly trends, and driver summary |
| **Responsive Design** | Mobile-friendly with collapsible sidebar |
| **CSRF Protection** | All POST forms include CSRF tokens |
| **Search & Filter** | Client-side search and status filter tabs on all tables |

---

## Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.1+ (vanilla, no framework) |
| **Database** | MySQL 8.0+ with PDO (prepared statements) |
| **Frontend** | HTML5, CSS3 (vanilla), JavaScript ES6+ |
| **AJAX** | Fetch API with custom `VRS.ajax` utility module |
| **Notifications** | Custom `VRS.notify` toast notification system |
| **Fonts** | Google Fonts (Inter) |
| **Server** | Apache (Laragon/XAMPP) with `.htaccess` URL rewriting |

---

## Architecture (MVC)

```
┌──────────────────────────────────────────────┐
│                   Browser                    │
│  (HTML/CSS/JS + AJAX + Toast Notifications)  │
└──────────────┬───────────────────────────────┘
               │ HTTP Request
               ▼
┌──────────────────────────────────────────────┐
│          index.php (Front Controller)        │
│  ► Loads config, core classes, starts session│
│  ► Dispatches to Router                      │
└──────────────┬───────────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────────┐
│              Router.php                      │
│  ► Maps URL → Controller::action            │
│  ► Supports both page routes and api/ routes │
└──────────────┬───────────────────────────────┘
               │
     ┌─────────┴─────────┐
     ▼                   ▼
┌────────────┐    ┌────────────┐
│ Controller │    │   Model    │
│  (Logic)   │◄──►│  (Data)    │
│            │    │  PDO/MySQL │
└─────┬──────┘    └────────────┘
      │
      ▼
┌────────────────┐
│     View       │
│  (HTML/PHP)    │
│  OR JSON resp  │
│  (for AJAX)    │
└────────────────┘
```

### MVC Responsibilities

| Layer | Role |
|---|---|
| **Model** | Database queries via PDO prepared statements. Each model extends the `Model` base class. |
| **View** | PHP template files organized by role (admin, requester, driver). Shared layouts in `layouts/`. |
| **Controller** | Business logic, authentication, CSRF verification. Returns views for page requests, JSON for AJAX. |

---

## Project Structure

```
Vehicle_Reservation/
├── index.php                    # Front controller (entry point)
├── .htaccess                    # Apache URL rewriting
├── config/
│   ├── app.php                  # App config, paths, security headers
│   └── database.php             # MySQL credentials
├── app/
│   ├── core/
│   │   ├── Database.php         # PDO singleton connection
│   │   ├── Model.php            # Base model (query, queryOne, execute)
│   │   ├── Controller.php       # Base controller (auth, CSRF, AJAX, JSON)
│   │   └── Router.php           # URL-to-controller routing + API routes
│   ├── controllers/
│   │   ├── AuthController.php   # Login, logout, session management
│   │   ├── DashboardController.php  # Dashboard views per role
│   │   ├── ReservationController.php # CRUD, approval, assignment
│   │   ├── VehicleController.php    # Fleet CRUD
│   │   ├── UserController.php       # Account management
│   │   ├── DriverController.php     # Dispatch & trip completion
│   │   └── ReportController.php     # Report generation
│   ├── models/
│   │   ├── User.php             # Auth, accounts
│   │   ├── Reservation.php      # Reservations, status workflow
│   │   ├── Vehicle.php          # Fleet records
│   │   ├── Driver.php           # Driver profiles, availability
│   │   ├── Approval.php         # Approval history
│   │   └── DispatchLog.php      # Trip logs (mileage, fuel)
│   └── views/
│       ├── layouts/
│       │   ├── header.php       # Shared header (CSRF meta, assets)
│       │   ├── footer.php       # Shared footer (JS loading)
│       │   └── sidebar.php      # Role-based navigation
│       ├── auth/
│       │   └── login.php        # Login page
│       ├── admin/
│       │   ├── dashboard.php    # Admin/ASD dashboard
│       │   ├── accounts.php     # User management
│       │   ├── account_form.php # Create/edit user
│       │   ├── vehicles.php     # Fleet list
│       │   ├── vehicle_form.php # Create/edit vehicle
│       │   ├── reservations.php # All reservations
│       │   ├── reservation_view.php # Detail + vehicle/driver assignment
│       │   ├── approvals.php    # Approve/reject cards
│       │   └── reports.php      # Report viewer
│       ├── requester/
│       │   ├── dashboard.php    # Requester home
│       │   ├── new_request.php  # Submit reservation
│       │   └── my_requests.php  # Track requests
│       ├── driver/
│       │   ├── dashboard.php    # Active trips + dispatch/complete
│       │   └── my_trips.php     # Trip history
│       └── errors/
│           └── 404.php          # Not found page
├── public/
│   ├── css/
│   │   ├── style.css            # Global styles (solid backgrounds)
│   │   ├── login.css            # Login page
│   │   ├── tables.css           # Table styling
│   │   ├── notifications.css    # Toast notification styles
│   │   └── responsive.css       # Mobile breakpoints
│   └── js/
│       ├── main.js              # Sidebar toggle, nav highlighting
│       ├── ajax.js              # VRS.ajax utility (fetch wrapper)
│       ├── notifications.js     # VRS.notify toast system
│       ├── login.js             # AJAX login
│       ├── dashboard.js         # Live clock, stat counters
│       ├── reservation.js       # AJAX reservation form
│       └── reports.js           # Print & CSV export
├── database/
│   ├── usep_vrs.sql             # Full schema + seed data
│   └── migrations/
│       ├── 001_create_users.sql
│       ├── 002_create_vehicles.sql
│       ├── 003_create_reservations.sql
│       ├── 004_create_approvals.sql
│       └── 005_create_dispatch_logs.sql
├── images/                      # Logo, icons
└── storage/
    └── logs/                    # Application error logs
```

---

## Database Schema (ERD)

```
                                    ┌─────────────────────┐
                     ┌──────────────│       REPORT        │
                     │  generates   │─────────────────────│
                     │              │ PK  report_id       │
              ┌──────┴──────┐       │ FK  user_id         │
              │   ADMIN     │       │     date_generated   │
              │─────────────│       │     description      │
              │ PK/FK user_id│      │     file_path        │
              │ first_name  │       │     report_content   │
              │ middle_name │       └─────────────────────┘
              │ last_name   │
              └──────┬──────┘
                     │ is
                     │
┌────────────────────┴────────────────────────────────────────────────────┐
│                            USER                                        │
│────────────────────────────────────────────────────────────────────────│
│ PK  user_id                                                            │
│     username         password_hash       email                         │
│     contact_number   role                date_created                   │
└───┬──────────────┬──────────────────────────────┬──────────────────────┘
    │ is           │ is                            │ is
    │              │                               │
┌───┴──────┐  ┌────┴──────────┐             ┌─────┴──────────┐
│  STAFF   │  │  REQUESTER    │             │    DRIVER      │
│──────────│  │───────────────│             │────────────────│
│PK/FK     │  │PK/FK user_id  │             │PK/FK user_id   │
│ user_id  │  │ first_name    │             │ first_name     │
│first_name│  │ middle_name   │  requests   │ middle_name    │
│middle_   │  │ last_name     │─────┐       │ last_name      │
│  name    │  │ department    │     │       │ status         │
│last_name │  └───────────────┘     │       └───────┬────────┘
│position  │                        │               │
└────┬─────┘                        │               │ assigns
     │ manages                      │               │
     │         ┌────────────────────┴───────┐  ┌────┴──────────────┐
     └────────►│       RESERVATION          │  │ RESERVATION_DRIVER│
               │────────────────────────────│  │──────────────────│
               │ PK  reservation_id         │  │PK/FK user_id     │
               │ FK  user_id                │  │PK/FK reservation │
               │ FK  vehicle_id             │◄─│      _id         │
               │     purpose                │  │ assigned_date    │
               │     destination            │  │ status           │
               │     departure_date         │  └──────────────────┘
               │     return_date            │
               │     time_out               │
               │     time_in                │       ┌──────────────────┐
               │     status                 │       │     VEHICLE      │
               │     remarks                │       │──────────────────│
               │     date_requested         │──────►│ PK  vehicle_id   │
               └────────────────────────────┘       │     vehicle_type │
                                          reserves  │     plate_number │
                                                    │     capacity     │
                                                    │     status       │
                                                    └──────────────────┘
```

### Relationships

| Relationship | Description |
|---|---|
| USER → ADMIN | A User **is** an Admin (1:1 subtype) |
| USER → STAFF | A User **is** a Staff member (1:1 subtype) |
| USER → REQUESTER | A User **is** a Requester (1:1 subtype) |
| USER → DRIVER | A User **is** a Driver (1:1 subtype) |
| ADMIN → REPORT | Admin **generates** Reports (1:N) |
| STAFF → RESERVATION | Staff **manages** Reservations (1:N) |
| REQUESTER → RESERVATION | Requester **requests** Reservations (1:N) |
| RESERVATION → VEHICLE | Reservation **reserves** a Vehicle (N:1) |
| DRIVER → RESERVATION_DRIVER | Driver **assigns** to Reservation (M:N junction) |
| RESERVATION → RESERVATION_DRIVER | Reservation **assigns** a Driver (M:N junction) |

### Tables

| Table | Purpose |
|---|---|
| `USER` | Base account table with login credentials and role designation |
| `ADMIN` | Admin profile — generates reports and manages system |
| `STAFF` | Staff profile — manages and oversees reservations |
| `REQUESTER` | Requester profile — submits reservation requests with department info |
| `DRIVER` | Driver profile — assigned to reservations for dispatch |
| `RESERVATION` | Vehicle reservation requests with trip details and status tracking |
| `VEHICLE` | Fleet registry with type, plate number, capacity, and availability status |
| `RESERVATION_DRIVER` | Junction table linking drivers to reservations with assignment date and status |
| `REPORT` | System-generated reports with descriptions and file references |

---

## Reservation Workflow

```
Requester submits request
        │
        ▼
   ┌─────────┐
   │ PENDING  │
   └────┬─────┘
        │ Unit Head reviews
        ▼
┌───────────────┐
│ UNIT_APPROVED │──── or ──── REJECTED
└───────┬───────┘
        │ ASD Coordinator reviews
        │ assigns Vehicle + Driver
        ▼
┌──────────────┐
│ ASD_APPROVED │──── or ──── REJECTED
└──────┬───────┘
       │ Driver starts trip
       ▼
┌────────────┐
│ DISPATCHED │
└─────┬──────┘
      │ Driver completes trip (logs mileage/fuel)
      ▼
┌───────────┐
│ COMPLETED │
└───────────┘
```

**Requester** can cancel a pending request → status becomes `CANCELLED`.

---

## AJAX Live Update System

All form operations use AJAX to eliminate page reloads:

| Operation | AJAX Behavior |
|---|---|
| **Login** | Submits via AJAX, shows toast on success/error, redirects on success |
| **Create/Edit Account** | AJAX submit → toast → redirect to accounts list |
| **Toggle Account Status** | AJAX toggle → updates badge and button in-place |
| **Create/Edit Vehicle** | AJAX submit → toast → redirect to vehicle list |
| **Submit Reservation** | AJAX submit → toast → redirect to my requests |
| **Cancel Reservation** | AJAX cancel → updates row status badge in-place |
| **Approve/Reject** | AJAX decision → toast → animated card removal |
| **Assign Vehicle + Driver** | AJAX submit → toast → page refresh to show updated status |
| **Start/Complete Trip** | AJAX submit → toast → page refresh for updated trip cards |

### Technical Implementation

- **`VRS.ajax`** module (`public/js/ajax.js`) wraps the Fetch API:
  - Auto-injects CSRF token from `<meta name="csrf-token">` into every request
  - Sets `X-Requested-With: XMLHttpRequest` header
  - Handles JSON parsing, error responses, and session expiry (401 → redirect)
  - `VRS.ajax.submitForm(form)` handles form serialization and submit button states

- **Controllers** detect AJAX via `isAjax()` method:
  - Page requests → render views via `$this->view()`
  - AJAX requests → return JSON via `$this->json()`
  - Both paths share the same business logic

---

## Real-Time Notification System

Toast notifications provide immediate visual feedback without page reload:

```javascript
VRS.notify.success('Reservation submitted!');
VRS.notify.error('Invalid credentials.');
VRS.notify.warning('Please fill in all fields.');
VRS.notify.info('Session will expire soon.');
```

### Features
- **Slide-in animation** from the top-right corner
- **Auto-dismiss** after 4 seconds with animated progress bar
- **Manual close** button on each toast
- **Stacking** — multiple toasts stack vertically
- **Color-coded** — green (success), red (error), amber (warning), blue (info)
- **Solid backgrounds** — no transparent overlays per project requirements
- **Responsive** — adjusts positioning on mobile

---

## Security

| Measure | Implementation |
|---|---|
| **SQL Injection Prevention** | All queries use PDO prepared statements via the `Model` base class |
| **CSRF Protection** | Token generated per session, validated on all POST requests (form field + AJAX header) |
| **Password Hashing** | bcrypt via `password_hash()` / `password_verify()` |
| **Session Security** | `session_regenerate_id()` on login, custom session name |
| **Input Sanitization** | `htmlspecialchars()` on all output, `strip_tags()` + `trim()` on input |
| **Security Headers** | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, `Referrer-Policy` |
| **Role-Based Access** | `requireRole()` enforced on every controller action |
| **Prepared Statements** | `PDO::ATTR_EMULATE_PREPARES => false` ensures real prepared statements |

---

## Installation

### Prerequisites
- **PHP 8.1+** with PDO MySQL extension
- **MySQL 8.0+** (or MariaDB 10.4+)
- **Apache** with `mod_rewrite` enabled (Laragon or XAMPP)

### Steps

1. **Clone or copy** the project into your web server directory:
   ```
   c:\laragon\www\Vehicle_Reservation\
   ```

2. **Create the database** — import the full schema and seed data:
   ```sql
   -- Open phpMyAdmin or MySQL CLI and run:
   SOURCE c:/laragon/www/Vehicle_Reservation/database/usep_vrs.sql;
   ```

3. **Configure database credentials** in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'usep_vrs');
   define('DB_USER', 'root');
   define('DB_PASS', '');   // Laragon default: empty
   ```

4. **Ensure `mod_rewrite`** is enabled (Laragon enables it by default).

5. **Access the system** at:
   ```
   http://localhost/Vehicle_Reservation/
   ```

---

## Test Credentials

| Role | Username | Password | Description |
|---|---|---|---|
| **Administrator** | `admin` | `admin@USeP2026` | Full system access |
| **ASD Coordinator** | `asdcoord` | `password123` | Approvals + vehicle/driver assignment |
| **Unit Head** | `unithead` | `password123` | First-level request approval |
| **Requester** | `msantos` | `password123` | Submit and track reservations |
| **Driver** | `jreyes` | `password123` | Start trips, log mileage |

---

## UI Design Principles

- **Solid backgrounds** — No transparent cards, shapes, or overlays
- **Maroon (#800000) & Gold (#F5C518)** — USeP brand palette
- **Inter font** — Clean, modern typography from Google Fonts
- **Micro-animations** — Fade-up stats, floating hero orb, panel slide-in
- **Responsive** — Collapsible sidebar, stacked layouts on mobile
- **Professional** — Clean card layouts, status badges, role tags

---

*© 2026 University of Southeastern Philippines. All rights reserved.*
