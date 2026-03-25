# Contact Form with Abuse Prevention

A production-ready, GDPR-compliant contact form system with multi-layered spam protection, security-hardened admin dashboard, and automated log anonymization. Battle-tested and hardened after a real-world security audit.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![GDPR Compliant](https://img.shields.io/badge/GDPR-Compliant-success)](https://gdpr.eu/)
[![Security Hardened](https://img.shields.io/badge/Security-Hardened_v5.0-brightgreen)](SECURITY.md)
[![Tests](https://img.shields.io/badge/Tests-31_Pass-brightgreen)](Documentation/20260325_security-fix-kontext/TESTING.md)

---

## About This Project

This project started as a learning exercise during my retraining as a Fachinformatiker für Anwendungsentwicklung and evolved into a production system protecting a real portfolio website. When the contact form was exploited as a spam relay in March 2026, I treated the incident as an opportunity: I conducted a structured security audit, identified 14 findings across 4 severity levels, and implemented fixes in a documented 6-phase hardening plan — all while keeping the system operational.

**What this project demonstrates:**

- Structured incident response and security auditing methodology
- Defense-in-depth implementation across DNS, HTTP, session, and application layers
- GDPR-compliant data handling with automated anonymization
- Clean documentation practices and reproducible test procedures
- Real-world PHP security beyond textbook examples

---

## Features

### Contact Form

PHPMailer SMTP integration with server-side captcha (session-based), CSRF protection, multi-layer spam detection (honeypot, timing analysis, keyword scoring, domain blacklist), and confirmation email rate limiting (1/address/24h). CORS locked to the configured origin domain.

### Admin Dashboard

Stateless HMAC-SHA256 authentication with IP-bound tokens (4h lifetime), Argon2id password hashing, brute-force protection (5 attempts / 15 min), CSRF tokens on all 8 admin forms, and a secured JSON API with PII masking.

### Abuse Prevention

IP blocklist/whitelist with subnet support and expiration, domain blacklist for disposable email providers, extended logging with spam score tracking, and automated IP anonymization after 14 days (cronjob-based, GDPR-compliant).

### Security Hardening (v5.0.0)

14 findings from a structured security audit addressed in 6 phases, covering CORS restrictions, CSRF protection, session hardening, information disclosure prevention, IP detection hardening, and DNS authentication (SPF/DKIM/DMARC). All fixes verified with 31 automated curl-based tests on the production server.

---

## Quick Start

**1. Clone and configure:**

```bash
git clone https://github.com/JoZapf/contact-form-abuse-prevention.git
cd contact-form-abuse-prevention
cp assets/php/.env.prod.example.v3 assets/php/.env.prod
```

**2. Set secrets in `.env.prod`:**

```bash
SMTP_HOST=mail.your-server.de
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=noreply@yourdomain.com
SMTP_PASS=your-smtp-password
RECIPIENT_EMAIL=admin@yourdomain.com
DASHBOARD_PASSWORD_HASH=<argon2id-hash>
DASHBOARD_SECRET=<openssl-rand-base64-32>
ALLOWED_ORIGIN=https://yourdomain.com
```

Generate the password hash:
```bash
php -r "echo password_hash('YourSecurePassword', PASSWORD_ARGON2ID) . PHP_EOL;"
```

**3. Set permissions and deploy:**

```bash
chmod 600 assets/php/.env.prod
chmod 755 assets/php/logs/ assets/php/data/
```

**4. Set up log anonymization cronjob:**

See [CRON-SETUP-GUIDE.md](Documentation/CRON-SETUP-GUIDE.md) for automated GDPR-compliant IP anonymization.

---

## File Structure

```
contact-form-abuse-prevention/
├── assets/
│   ├── php/
│   │   ├── contact-php-handler.php      # Form handler (v4.3.0)
│   │   ├── ContactFormValidator-v2.php   # Spam detection engine
│   │   ├── AbuseLogger.php              # Logging (v1.1.0, hardened IP detection)
│   │   ├── BlocklistManager.php         # IP blocklist/whitelist
│   │   ├── ExtendedLogger.php           # GDPR-compliant logging
│   │   ├── helpers.php                  # Centralized env() + verifyToken()
│   │   ├── LoginRateLimiter.php         # Brute-force protection
│   │   ├── dashboard-login.php          # Auth with Argon2id + rate limiting
│   │   ├── dashboard.php                # Admin UI (CSRF-protected)
│   │   ├── dashboard-blocklist.php      # Blocklist management (CSRF-protected)
│   │   ├── dashboard-api.php            # JSON API (HMAC auth + PII masking)
│   │   ├── .htaccess                    # Access control (v2.0.0)
│   │   ├── security-tests.sh            # 31 automated security tests
│   │   ├── data/                        # Runtime data (gitignored)
│   │   └── logs/                        # Logs (gitignored)
│   ├── css/contact-form.css
│   ├── js/contact-form-logic.js
│   └── html/contact-form-wrapper.html
├── cron/anonymize-logs.php              # GDPR anonymization cronjob
├── Documentation/                       # Detailed docs (see below)
├── CHANGELOG.md                         # Version history
├── MIGRATION.md                         # v5.0.0 migration details
├── SECURITY.md                          # Security policy & audit status
├── CONTRIBUTING.md                      # Development guidelines
└── LICENSE                              # MIT
```

---

## Documentation

| Document | Purpose |
|----------|---------|
| [CHANGELOG.md](CHANGELOG.md) | Complete version history |
| [SECURITY.md](SECURITY.md) | Security policy, audit status, OWASP coverage, responsible disclosure |
| [MIGRATION.md](MIGRATION.md) | v5.0.0 file-by-file migration details with audit IDs |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Development setup, coding standards, PR process |
| [HMAC-AUTHENTICATION.md](Documentation/HMAC-AUTHENTICATION.md) | Token authentication system |
| [CSRF-PROTECTION.md](Documentation/CSRF-PROTECTION.md) | CSRF implementation details |
| [CRON-SETUP-GUIDE.md](Documentation/CRON-SETUP-GUIDE.md) | Automated log anonymization setup |
| [PRODUCTION-vs-GITHUB.md](Documentation/PRODUCTION-vs-GITHUB.md) | Deployment differences |



---

## Security Overview

This system implements defense-in-depth across multiple layers:

**DNS layer:** SPF, DKIM, and DMARC configured to prevent email spoofing.

**HTTP layer:** `.htaccess` blocks access to all diagnostic endpoints, environment files, PHP class files, and configuration documents. Directory listing disabled.

**Session layer:** Secure session configuration with `HttpOnly`, `Secure`, `SameSite=Strict` flags and strict mode. CSRF tokens are one-time-use with `hash_equals()` validation.

**Application layer:** Server-side captcha, CORS domain lock, input sanitization, spam scoring, IP rate limiting, confirmation email rate limiting, and HMAC token authentication with IP binding.

**Data layer:** Automated IP anonymization after 14 days, PII masking in API responses, and encrypted SMTP transport.

For the full security policy, OWASP Top 10 coverage matrix, and responsible disclosure process, see [SECURITY.md](SECURITY.md).

---

## Testing

All security measures are verified by automated tests:

```bash
# Run on production server via SSH
bash security-tests.sh
```

The test suite covers access control (19 blocked paths → 403), CORS header validation, CSRF token generation, session cookie flags, and directory listing prevention. See [TESTING.md](Documentation/TESTING.md) for the full test matrix and expected results.

---

## Disclaimer

This project is a learning and portfolio project. It is provided as-is, without warranty of any kind. The security measures described in this documentation reflect the author's best efforts at the time of implementation but do not constitute a professional security audit or guarantee. Use at your own risk. The author assumes no liability for damages resulting from the use of this software.

## License

MIT — see [LICENSE](LICENSE).

---

## About

Created by [Jo Zapf](https://jozapf.de)

This project is part of my portfolio demonstrating practical security skills, structured problem-solving, and thorough documentation practices. Every finding is documented, every fix is tested, and every decision is explained.

**Current version:** 5.0.0 (March 2026)  
**Security status:** Hardened — 14/14 findings addressed, 31 tests passing
