# ModernQuiz Security Documentation

## Overview

This document outlines the security measures implemented in ModernQuiz to protect user data and prevent common web vulnerabilities.

---

## ✅ Implemented Security Features

### 1. Authentication & Authorization

#### **Password Security**
- ✅ **BCrypt Hashing**: Passwords hashed with `password_hash()` using `PASSWORD_BCRYPT` with cost factor 12
- ✅ **Password Verification**: Using `password_verify()` for constant-time comparison
- ✅ **Password Rehashing**: Automatic rehashing if cost factor updated
- ✅ **Strong Password Requirements**:
  - Minimum 8 characters
  - Must contain uppercase, lowercase, numbers
  - Must contain at least 2 special characters

**Files:**
- `src/modules/auth/Register.php:76-77` - Password hashing
- `src/modules/auth/Login.php:114-118` - Password verification & rehashing
- `src/modules/auth/Auth.php:18-29` - Password validation rules

#### **Session Management**
- ✅ **Cryptographically Secure Tokens**: Using `random_bytes(32)` for session tokens
- ✅ **Device Fingerprinting**: Browser + IP + User-Agent hash to detect hijacking
- ✅ **Session Validation**: Device hash verified on every request
- ✅ **HTTPOnly Cookies**: Session tokens stored in httpOnly cookies
- ✅ **SameSite Attribute**: Set to 'Lax' to prevent CSRF
- ✅ **Secure Flag**: Enabled in HTTPS environments
- ✅ **Session Expiration**: 30-day expiry with automatic cleanup

**Files:**
- `src/core/SessionManager.php` - Complete session management
- `src/core/AuthMiddleware.php:44-62` - Session validation & device checking
- `public/api/index.php:172-183` - Cookie security settings

#### **API Authentication**
- ✅ **Middleware Protection**: All endpoints (except /auth/*) require authentication
- ✅ **Token in Header or Cookie**: Supports `Authorization: Bearer <token>` or `session_token` cookie
- ✅ **Authorization Checks**: Verify users can only access their own resources
- ✅ **Admin Role Verification**: Admin endpoints check user role

**Files:**
- `src/core/AuthMiddleware.php` - Authentication middleware
- `public/api/index.php:270-275` - Global auth enforcement
- `public/api/index.php:315-322` - Resource ownership verification example

---

### 2. Injection Prevention

#### **SQL Injection Protection**
- ✅ **Prepared Statements**: 100% coverage - all queries use PDO prepared statements
- ✅ **Parameterized Queries**: No string concatenation in SQL
- ✅ **Type Validation**: Input parameters validated and cast to correct types

**Example:**
```php
// src/modules/auth/Login.php:104-112
$stmt = $this->db->prepare("
    SELECT id, username, email, password_hash, email_verified, is_active, coins, points, level, avatar
    FROM users
    WHERE (username = ? OR email = ?)
    LIMIT 1
");
$stmt->execute([$identifier, $identifier]);
```

---

### 3. Cross-Site Scripting (XSS) Protection

#### **Output Escaping**
- ✅ **Sanitization Helper**: `sanitizeOutput()` function for escaping HTML
- ✅ **JSON API Responses**: All API responses in JSON (auto-escaped by browser)
- ✅ **Content-Type Headers**: Explicit `application/json; charset=utf-8`

**Files:**
- `public/api/index.php:115-124` - sanitizeOutput() helper function

#### **Security Headers**
- ✅ `X-Content-Type-Options: nosniff` - Prevent MIME sniffing
- ✅ `X-Frame-Options: DENY` - Prevent clickjacking
- ✅ `X-XSS-Protection: 1; mode=block` - Legacy XSS filter
- ✅ `Strict-Transport-Security` - Force HTTPS

**Files:**
- `public/api/index.php:11-20` - Security headers

---

### 4. Brute Force Protection

#### **Rate Limiting**
- ✅ **Failed Login Tracking**: IP-based login attempt logging
- ✅ **Lockout Mechanism**: 5 failed attempts = 15-minute lockout
- ✅ **Automatic Cleanup**: Failed attempts cleared on successful login
- ✅ **Database Table**: `login_attempts` tracks IP, identifier, timestamp

**Files:**
- `src/modules/auth/Login.php:129-154` - Rate limiting implementation
- `src/database/migrations/20250105_000001_create_login_attempts_table.php` - Database schema

---

### 5. Email Security

#### **Email Validation**
- ✅ **Format Validation**: Using `filter_var($email, FILTER_VALIDATE_EMAIL)`
- ✅ **Lowercase Normalization**: All emails stored in lowercase
- ✅ **Unique Constraint**: Database-level email uniqueness
- ✅ **Email Verification**: Token-based verification before account activation

**Files:**
- `src/modules/auth/Register.php:38-42` - Email validation
- `src/modules/auth/Register.php:260-272` - Email verification

---

### 6. Input Validation

#### **Type Validation**
- ✅ **Integer Validation**: `validateInt()` with min/max bounds
- ✅ **String Sanitization**: `trim()` on all string inputs
- ✅ **Email Validation**: Proper email format checking
- ✅ **Username Validation**: Alphanumeric + underscore only, 3-30 chars

**Example:**
```php
// public/api/index.php:97-110
function validateInt($value, string $name, int $min = null, int $max = null): int {
    $intValue = filter_var($value, FILTER_VALIDATE_INT);
    if ($intValue === false) {
        sendError("Invalid $name: must be an integer");
    }
    if ($min !== null && $intValue < $min) {
        sendError("Invalid $name: must be at least $min");
    }
    if ($max !== null && $intValue > $max) {
        sendError("Invalid $name: must be at most $max");
    }
    return $intValue;
}
```

---

### 7. Error Handling

#### **Secure Error Messages**
- ✅ **Production Mode**: Generic error messages (no stack traces)
- ✅ **Debug Mode**: Detailed errors only when `APP_DEBUG=true`
- ✅ **Error Logging**: All errors logged to server logs
- ✅ **User Enumeration Prevention**: Same message for "user not found" and "wrong password"

**Files:**
- `public/api/index.php:614-626` - Error handling with debug mode check

---

### 8. Referral System Security

#### **Referral Code Generation**
- ✅ **Collision Detection**: Check for duplicate codes before using
- ✅ **Fallback to random_bytes**: If collisions persist
- ✅ **Validation**: Verify referral code exists before processing

**Files:**
- `src/modules/auth/Register.php:190-210` - Secure referral code generation

---

### 9. Database Security

#### **Connection Security**
- ✅ **PDO with Error Mode**: `PDO::ERRMODE_EXCEPTION` for proper error handling
- ✅ **Persistent Connections**: Disabled by default for better resource management
- ✅ **Charset**: UTF-8 enforced at connection level

**Files:**
- `src/core/Database.php` - Database connection singleton

---

## 🚧 Additional Security Recommendations

### For Production Deployment:

1. **HTTPS Enforcement**
   - Ensure all traffic is over HTTPS
   - Set `Strict-Transport-Security` with longer `max-age`

2. **Environment Variables**
   - Never commit `.env` file
   - Use strong, unique database passwords
   - Rotate secrets regularly

3. **Content Security Policy (CSP)**
   ```php
   header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
   ```

4. **CSRF Tokens** (Optional Enhancement)
   - Implement CSRF tokens for state-changing operations
   - Especially important if CORS is restricted

5. **Database Permissions**
   - Use separate database users for different environments
   - Grant only necessary privileges (no DROP, CREATE in production)

6. **Logging & Monitoring**
   - Implement security event logging
   - Monitor for suspicious patterns (multiple failed logins, etc.)
   - Set up alerts for critical errors

7. **Backup Strategy**
   - Regular automated database backups
   - Store backups securely (encrypted, off-site)
   - Test restore procedures

8. **Update Dependencies**
   ```bash
   composer update
   ```
   - Keep PHP and all dependencies up to date
   - Monitor security advisories

---

## 🔒 Secure Configuration

### .env Settings for Production

```env
# Database
DB_HOST=localhost
DB_NAME=modernquiz
DB_USER=modernquiz_user  # Use dedicated user with limited privileges
DB_PASS=<STRONG_RANDOM_PASSWORD>

# Application
APP_DEBUG=false           # CRITICAL: Must be false in production
APP_ENV=production
APP_URL=https://yourdomain.com

# Email
MAIL_FROM=noreply@yourdomain.com

# Security
SESSION_LIFETIME=30       # Days
```

---

## 🛡️ Security Checklist for Go-Live

- ✅ All passwords properly hashed with bcrypt
- ✅ Authentication required on all protected endpoints
- ✅ Authorization checks verify resource ownership
- ✅ All database queries use prepared statements
- ✅ Input validation on all user inputs
- ✅ Rate limiting on login attempts
- ✅ Security headers configured
- ✅ HTTPOnly cookies for session tokens
- ✅ Error messages don't leak sensitive information
- ⚠️  HTTPS enforced (configure on web server)
- ⚠️  DEBUG mode disabled (`APP_DEBUG=false`)
- ⚠️  Strong database password set
- ⚠️  .env file not in version control
- ⚠️  Regular backups configured
- ⚠️  Monitoring & logging set up

---

## 📞 Security Contact

If you discover a security vulnerability, please report it responsibly by contacting the development team directly rather than creating a public issue.

---

## 🔄 Last Security Audit

**Date:** 2025-01-05
**Result:** Major security vulnerabilities fixed, system ready for production with recommended additional measures.
