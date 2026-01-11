# Laravel 12 API Frame (Modular)

A Laravel 12 API starter/frame built with a **modular architecture**, **token authentication**, and **RBAC (roles & permissions)**. It includes a ready-to-use **OTP module** for phone verification and a clean, consistent API response style for building admin panels and SPA/mobile clients.

> This repository contains **Laravel API only** (no frontend).

---

## Features

- **Laravel 12** API foundation
- **Modular structure** (feature-based modules such as Auth, User, RBAC, OTP, Dashboard)
- **Token authentication** using **Laravel Sanctum**
- **RBAC** using **spatie/laravel-permission**
    - Multiple roles per user
    - Permissions are **seeded/fixed**, and roles can be mapped to permissions
- **OTP module** (custom)
    - OTP request + verify endpoints
    - Supports cooldown / expiry / remaining attempts per day (based on your implementation)
- **Admin user management**
    - List / show / update / delete users
- **Dashboard metrics** endpoint (extendable)
- Consistent API response shape (`success`, `message`, `data`, `meta`)

---

## Project Structure (Modular)

This project is organized by **modules**, so each feature has its own controllers, routes, requests, services, etc.

Typical module layout:

```
Modules/
  Auth/
    Http/Controllers/
    Http/Requests/
    routes/api.php
  User/
    Http/Controllers/
    Http/Requests/
    routes/api.php
  RBAC/
    Http/Controllers/
    routes/api.php
  OTP/
    Http/Controllers/
    routes/api.php
  Dashboard/
    Http/Controllers/
    routes/api.php
```

### Why modules?
- Keeps features isolated and maintainable
- Makes scaling easier (add/remove features cleanly)
- Helps keep controllers/routes small and feature-focused

---

## Requirements

- PHP 8.2+ (recommended)
- Composer
- MySQL / MariaDB (or any supported DB)
- (Optional) Redis for caching/rate-limit improvements

---

## Installation

### 1) Clone & install dependencies
```bash
git clone <your-repo-url>
cd laravel12-api-frame
composer install
```

### 2) Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials.

### 3) Run migrations & seeders
```bash
php artisan migrate --seed
```

> Seeders should create initial roles/permissions (RBAC) and any required default data.

### 4) Run the server
```bash
php artisan serve
```

API base URL:
- `http://127.0.0.1:8000/api`

---

## Authentication (Sanctum)

This API uses **Laravel Sanctum** for token-based authentication.

Typical flow:
1. Client logs in (`POST /api/auth/login`)
2. API returns token + user
3. Client sends token on every request:

```
Authorization: Bearer <token>
Accept: application/json
```

---

## RBAC (Roles & Permissions)

This project uses **spatie/laravel-permission**.

### Key points
- A user can have **multiple roles**
- Permissions are **seeded/fixed**
- Roles can be mapped to permissions dynamically through RBAC endpoints (admin-only)

### Recommended authorization style
Use middleware in routes/controllers:
- `auth:sanctum`
- `role:<role-name>`
- `permission:<permission-name>`

> Always enforce authorization in the API even if the frontend hides UI elements.

---

## OTP Module (Custom)

The OTP module is designed for phone verification flows (e.g., registration verification).

Typical flow:
1. Register user (creates account)
2. Request OTP
3. Verify OTP to mark phone as verified

Depending on your implementation, the OTP response may include:
- `cooldown_in`
- `expires_in`
- `remaining_today`

> OTP sending integration (SMS gateway) can be swapped as needed.

---

## API Endpoints (Main)

Below are the main API routes available (based on `php artisan route:list`).

### Auth
- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET  /api/me`
- `PATCH /api/me`

### OTP
- `POST /api/otp/request`
- `POST /api/otp/verify`

### Admin (Users)
- `GET    /api/admin/users`
- `GET    /api/admin/users/{user}`
- `PATCH  /api/admin/users/{user}`
- `DELETE /api/admin/users/{user}`

### Dashboard (Admin)
- `GET /api/admin/dashboard/metrics`

### RBAC
Roles:
- `GET    /api/rbac/roles`
- `POST   /api/rbac/roles`
- `GET    /api/rbac/roles/{role}`
- `PATCH  /api/rbac/roles/{role}`
- `DELETE /api/rbac/roles/{role}`

Permissions:
- `GET    /api/rbac/permissions`
- `POST   /api/rbac/permissions`
- `GET    /api/rbac/permissions/{permission}`
- `PATCH  /api/rbac/permissions/{permission}`
- `DELETE /api/rbac/permissions/{permission}`

Assignments:
- `GET  /api/rbac/roles/{role}/permissions`
- `POST /api/rbac/roles/{role}/permissions`
- `GET  /api/rbac/users/{user}/roles`
- `POST /api/rbac/users/{user}/roles`

> **Security note:** RBAC and admin endpoints should be protected by `auth:sanctum` and admin role/permission middleware in the route definitions.

---

## API Response Convention

Most endpoints are expected to return a consistent JSON format:

### Success
```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

### Error
```json
{
  "success": false,
  "message": "Something went wrong",
  "errors": {}
}
```

---

## Development Notes

### Route inspection
```bash
php artisan route:list
```

### Cache clear
```bash
php artisan optimize:clear
```

### Run tests (if available)
```bash
php artisan test
```

---

## Security Checklist (Recommended)

- Protect admin and RBAC routes with:
    - `auth:sanctum`
    - `role:admin` and/or `permission:*`
- Do not expose OTP in production responses/logs
- Configure CORS correctly if used by an SPA/mobile client
- Use HTTPS in production

---
