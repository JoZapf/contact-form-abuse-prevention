# Changelog

All notable changes to this project are documented in this file.  
Format follows [Keep a Changelog](https://keepachangelog.com/). Versioning follows [Semantic Versioning](https://semver.org/).

---

## [5.0.0] — 2026-03-25

### Comprehensive Security Hardening

Following a real-world abuse incident (form relay exploitation + DNS spoofing), the entire system underwent a structured security audit and 6-phase hardening process. 14 findings were identified, prioritized by severity, and addressed across 10 files (7 updated, 3 new).

**All 31 automated security tests pass on production** (30 PASS, 1 expected WARN).

#### Added

- `helpers.php` — Centralized `env()` and `verifyToken()` replacing 4 separate implementations
- `LoginRateLimiter.php` — File-based brute-force protection (5 attempts / 15 min per IP)
- `security-tests.sh` — 30+ curl-based functional tests covering all hardening phases
- Session-based CSRF token for contact form (one-time-use, `hash_equals()` validation)
- Server-side captcha storage (solution in PHP session instead of HTML hidden field)
- Confirmation email rate limiting (1 per email address per 24h, SHA-256 hashed)
- CSRF tokens in all 8 dashboard POST forms (4× `dashboard.php`, 4× `dashboard-blocklist.php`)
- Dashboard login brute-force protection with IP-based lockout
- HMAC token IP binding and reduced lifetime (24h → 4h)
- Session hardening (`cookie_httponly`, `cookie_secure`, `cookie_samesite=Strict`, `use_strict_mode`)

#### Changed

- **CORS:** `Access-Control-Allow-Origin: *` → `https://jozapf.de` (configurable via `ALLOWED_ORIGIN`)
- **`.htaccess`:** Complete rewrite (v1.0.0 → v2.0.0) — blocks diagnostic endpoints, env files, PHP classes, markdown files; adds `Options -Indexes`
- **`AbuseLogger.php`:** IP detection hardened — removed spoofable `X-Forwarded-For`/`X-Real-IP`, uses only `REMOTE_ADDR` + `CF-Connecting-IP`
- **Dashboard login:** Removed plaintext password default (`admin123`), supports Argon2id hash via `DASHBOARD_PASSWORD_HASH`
- **Dashboard auth:** Klartext comparison `===` replaced with `hash_equals()` (timing-safe)

#### Security

- DNS hardening: SPF corrected (`?all` → `~all`), DMARC record added (`p=quarantine`), DKIM validated
- SMTP credentials rotated after information disclosure was identified and sealed
- Domain not on any blacklists (verified via mxtoolbox, 2026-03-25)

#### Migration Notes

- `ALLOWED_ORIGIN` must be set in `.env.prod` (or defaults to `https://jozapf.de`)
- `DASHBOARD_PASSWORD_HASH` should replace `DASHBOARD_PASSWORD` — generate with: `php -r "echo password_hash('...', PASSWORD_ARGON2ID);"`
- Frontend CSRF/Captcha integration pending (backend runs in migration mode — see `MIGRATION.md`)
- DMARC policy upgrade to `reject` planned for ~2026-04-07

---

## [4.2.0] — 2025-10-05

### AP-02: Dashboard CSRF Protection

- Double Submit Cookie pattern with JWT token binding
- 32-byte cryptographically secure CSRF tokens
- All dashboard admin actions protected (block/unblock/whitelist)
- Timing-safe token validation (`hash_equals()`)
- HTTP 403 + audit logging on failed validation

---

## [4.1.0] — 2025-10-05

### AP-01: Dashboard API Security

- HMAC-SHA256 token-based authentication for dashboard API
- CORS restricted to configured origin (no wildcards on API endpoint)
- PII protection: email masking in API responses (`u***@domain.com`)
- Security headers: `Cache-Control`, `X-Content-Type-Options`
- Fail-fast configuration pattern

---

## [4.0.0] — 2025-10-04

### Domain Blacklist & Dashboard Improvements

- Domain blacklist for disposable/spam email providers
- Dashboard UI improvements and statistics display
- Post-Redirect-Get pattern for form submissions
- Blocklist duration tracking and expiration
- One-click IP blocking from submission logs

---

## [3.0.0] — 2025-09

### HMAC Authentication & Log Anonymization

- HMAC token-based stateless dashboard authentication
- Automated IP anonymization via cronjob (GDPR compliance, 14-day retention)
- Secure cookie handling (`HttpOnly`, `Secure`, `SameSite=Strict`)
- Execution logging and audit trail for anonymization

---

## [2.0.0] — 2025-09

### Extended Logging & Abuse Prevention

- Multi-layer spam detection (honeypot, timing, keywords, domain blacklist)
- IP blocklist and whitelist management with subnet support
- Extended logging system with GDPR-compliant storage
- Rate limiting for form submissions
- Browser fingerprinting (non-invasive)

---

## [1.0.0] — 2025-08

### Initial Release

- PHPMailer SMTP integration
- Server-side captcha (math-based)
- Basic input validation and sanitization
- Contact form with confirmation email
- Privacy policy page

---

[5.0.0]: https://github.com/JoZapf/contact-form-abuse-prevention/compare/v4.2.0...v5.0.0
[4.2.0]: https://github.com/JoZapf/contact-form-abuse-prevention/compare/v4.1.0...v4.2.0
[4.1.0]: https://github.com/JoZapf/contact-form-abuse-prevention/compare/v4.0.0...v4.1.0
[4.0.0]: https://github.com/JoZapf/contact-form-abuse-prevention/compare/v3.0.0...v4.0.0
[3.0.0]: https://github.com/JoZapf/contact-form-abuse-prevention/compare/v2.0.0...v3.0.0
[2.0.0]: https://github.com/JoZapf/contact-form-abuse-prevention/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/JoZapf/contact-form-abuse-prevention/releases/tag/v1.0.0
