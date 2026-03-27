# Security Policy

## Overview

This project implements defense-in-depth security practices across DNS, HTTP, session, and application layers. The system underwent a comprehensive security audit in March 2026 following a real-world abuse incident, resulting in 14 findings that were addressed in a structured 6-phase hardening plan.

**Current Security Status:** 🟢 Hardened (v5.1.0 — 14/14 findings addressed + Phase 7 spam filtering)  
**Last Audit:** March 2026  
**Automated Tests:** 43 passing (43 PASS, 0 FAIL, 1 expected WARN)

---

## Supported Versions

| Version | Supported | Security Status |
|---------|-----------|-----------------|
| 5.1.x | ✅ Yes | 🟢 Fully hardened + spam filtering |
| 5.0.x | ⚠️ Limited | 🟠 Missing Phase 7 spam filtering |
| 4.2.x | ❌ No | 🔴 Upgrade strongly recommended |
| < 4.2 | ❌ No | 🔴 End of Life |

**Recommendation:** Always use the latest version (5.1.x). Versions prior to 5.0.0 have known security gaps that are addressed in the current release.

---

## Security Architecture

### DNS Layer

Email authentication is configured to prevent domain spoofing:

- **SPF:** Authorizes only the domain's own mail servers; unauthorized senders receive a softfail
- **DKIM:** Cryptographic signing validated for all outgoing mail
- **DMARC:** Policy enforced with aggregate reporting; upgrade to `reject` policy planned after monitoring period

### HTTP Layer — Access Control

The `.htaccess` configuration (v2.1.0) enforces strict access control:

- **Diagnostic endpoints** are blocked from public access — these include any development or debugging scripts that could expose server configuration
- **Environment files** (all `.env*` variants) are denied via HTTP
- **PHP class files** used internally via `require_once` are blocked from direct HTTP requests
- **Configuration documents** (Markdown, shell scripts) are denied
- **Directory listing** is disabled (`Options -Indexes`)

Only the form handler, dashboard login, dashboard UI, and the API endpoint are publicly reachable.

### Session Layer

PHP sessions are configured with hardened flags:

- `cookie_httponly` — prevents JavaScript access to session cookies
- `cookie_secure` — enforces HTTPS-only cookie transmission
- `cookie_samesite=Strict` — prevents cross-site cookie transmission
- `use_strict_mode` — rejects uninitialized session IDs

CSRF tokens are generated per session, validated with `hash_equals()` (timing-safe), and consumed after use (one-time tokens).

### Application Layer

**Contact form protection:**

- CORS locked to the configured origin domain (no wildcards)
- Session-based CSRF token required for form submission
- Server-side captcha — solution stored in PHP session, never exposed to the client
- Multi-layer spam detection: honeypot fields, submission timing analysis, keyword scoring, suspicious prefix detection (hard-block and soft-flag), suspicious TLD scoring, domain blacklist (72,000+ disposable domains from [disposable-email-domains](https://github.com/disposable-email-domains/disposable-email-domains), CC0 license), and real-time disposable email API verification via [DeBounce](https://disposable.debounce.io/) (free, no API key required)
- Confirmation email rate limiting: maximum 1 per email address per 24 hours (addresses stored as SHA-256 hashes)

**Dashboard protection:**

- Argon2id password hashing (no plaintext storage or comparison)
- Stateless HMAC-SHA256 authentication tokens with IP binding and 4-hour lifetime
- Brute-force protection: 5 failed attempts per IP triggers a 15-minute lockout
- CSRF tokens in all 8 POST forms across dashboard and blocklist management
- PII masking in API responses (email addresses shown as `u***@domain.com`)

**IP handling:**

- Client IP determined from `REMOTE_ADDR` only (no trust for spoofable proxy headers like `X-Forwarded-For`)
- `CF-Connecting-IP` supported for Cloudflare deployments
- Automated IP anonymization after 14 days (cronjob-based, GDPR-compliant)

### Data Layer

- SMTP transport via TLS/SSL (PHPMailer)
- Credentials stored in `.env.prod` (excluded from version control, blocked via `.htaccess`)
- Log files protected by dedicated `.htaccess` with script execution disabled
- JSON data files (blocklist, whitelist) protected by file permissions

---

## Security Audit Summary (March 2026)

A structured security audit identified 14 findings across 4 severity levels. All findings were addressed in a 6-phase deployment over 48 hours while keeping the production system operational.

### Findings by Severity

| Severity | Count | Scope |
|----------|-------|-------|
| 🔴 Critical | 2 | Cross-origin request policy, cross-site request forgery protection |
| 🟠 High | 5 | Captcha implementation, brute-force protection, CSRF on admin forms, information disclosure, authentication defaults |
| 🟡 Medium | 5 | Access control gaps, IP detection, code duplication, token binding, credential file exposure |
| 🟢 Low | 2 | Session configuration, email rate limiting |

### Hardening Phases

| Phase | Scope | Layer |
|-------|-------|-------|
| 1 | Access control — blocked diagnostic endpoints, environment files, internal classes | HTTP |
| 2 | CORS restriction — origin locked to configured domain | Application |
| 3 | CSRF protection — session-based one-time tokens for contact form | Session / Application |
| 4 | Captcha hardening — moved solution from client HTML to server session | Application |
| 5 | Dashboard hardening — brute-force protection, CSRF on all forms, password hash enforcement, centralized helpers, token IP binding | Application |
| 6 | Cleanup — IP detection hardened, confirmation email rate limiting, code consolidation | Application |
| 7 | Disposable email detection — 3-layer spam filtering: suspicious prefix/TLD scoring, 72k+ domain blacklist (auto-updated weekly), DeBounce API check with fallback | Application |

Additionally, DNS authentication (SPF/DKIM/DMARC) was configured as a prerequisite before the application-level fixes.

---

## Automated Security Tests

The project includes a test suite (`security-tests.sh`) that verifies all hardening measures on the production server. Tests are designed to confirm protections without revealing specific attack vectors.

### Test Coverage

| Area | Tests | What is verified |
|------|-------|------------------|
| Access control | 19 paths | Blocked resources return HTTP 403 |
| Endpoint availability | 2 paths | Form handler and login remain reachable |
| CORS policy | 1 | Origin header returns configured domain, not wildcard |
| CSRF / Init | 1 | Init endpoint returns valid JSON with token and captcha |
| Cross-origin rejection | 1 | Foreign origin is not reflected in response headers |
| Directory listing | 1 | No "Index of" response on directory access |
| Document blocking | 1+ | Configuration documents return 403 |
| Session security | 1 | Cookie flags include HttpOnly, Secure, SameSite |
| Blacklist file protection | 4 | domain-blacklist.txt, custom list, JSON data files return 403 |
| Spam prefix blocking | 3 | Full submit flow: spam@, test@, fake@ prefixes are blocked |
| Domain blacklist | 3 | Full submit flow: mailinator, guerrillamail, yopmail are blocked |
| Disposable API | 2 | DeBounce API reachable, correct classification of disposable/legit |

**Latest result:** 43 PASS, 0 FAIL, 1 expected WARN (CSRF migration mode — contact form CSRF only enforced when init endpoint was called; strict mode pending frontend update).

### Running Tests

```bash
# Upload to server and execute via SSH
scp security-tests.sh user@server:~/
ssh user@server 'bash ~/security-tests.sh'
```

### Verification Examples

The following patterns can be used to verify hardening on any deployment:

```bash
# Access control: sensitive paths must return 403
curl -s -o /dev/null -w "%{http_code}" https://yourdomain.com/assets/php/.env.prod
# Expected: 403

# CORS: must return specific domain, not wildcard
curl -s -D - -o /dev/null -X OPTIONS \
  https://yourdomain.com/assets/php/contact-php-handler.php \
  | grep -i "access-control-allow-origin"
# Expected: Access-Control-Allow-Origin: https://yourdomain.com

# CSRF init: must return JSON with token
curl -s https://yourdomain.com/assets/php/contact-php-handler.php?init=1
# Expected: JSON with csrf_token and captcha fields

# Cross-origin: foreign origin must not be reflected
curl -s -D - -o /dev/null -H "Origin: https://evil.com" \
  https://yourdomain.com/assets/php/contact-php-handler.php \
  | grep -i "access-control-allow-origin"
# Expected: https://yourdomain.com (NOT https://evil.com)

# Directory listing: must not show file index
curl -s https://yourdomain.com/assets/php/ | grep -i "index of"
# Expected: no output
```

---

## OWASP Top 10 Coverage (2021)

| # | Category | Status | Implementation |
|---|----------|--------|----------------|
| A01 | Broken Access Control | ✅ Protected | `.htaccess` v2.1.0, HMAC auth, CORS domain lock, CSRF tokens |
| A02 | Cryptographic Failures | ✅ Protected | Argon2id hashing, HMAC-SHA256, HTTPS enforcement, secure cookies |
| A03 | Injection | ✅ Protected | Input sanitization (`htmlspecialchars` + `ENT_QUOTES`), no SQL (JSON storage) |
| A04 | Insecure Design | ✅ Protected | Defense-in-depth, fail-fast configuration, no silent defaults |
| A05 | Security Misconfiguration | ✅ Protected | No wildcard CORS, no directory listing, diagnostic endpoints blocked, env files protected |
| A06 | Vulnerable Components | ✅ Monitored | Composer audit, PHPMailer updates |
| A07 | Auth & Session Failures | ✅ Protected | Argon2id, IP-bound tokens, 4h lifetime, brute-force lockout, session hardening |
| A08 | Data Integrity Failures | ✅ Protected | CSRF tokens (contact form + all 8 dashboard forms), HMAC signatures |
| A09 | Logging & Monitoring | ✅ Protected | Extended logging, audit trails, GDPR-compliant anonymization |
| A10 | SSRF | ✅ Protected | Outbound API calls (DeBounce) use hardcoded URL — no user-controlled destinations. Timeout enforced (2s) |

### CWE Coverage

| CWE | Vulnerability | Protection |
|-----|---------------|------------|
| CWE-79 | Cross-Site Scripting | Input sanitization, `ENT_QUOTES`, `HttpOnly` cookies |
| CWE-200 | Information Exposure | Diagnostic endpoints blocked, PII masking in API, env files protected |
| CWE-287 | Authentication Issues | Argon2id, HMAC tokens, IP binding, brute-force protection |
| CWE-352 | Cross-Site Request Forgery | Session-based one-time tokens with `hash_equals()` validation |
| CWE-639 | Insecure Direct Object References | Blocklist/whitelist input validation |
| CWE-778 | Insufficient Logging | Comprehensive audit logging for auth, CSRF, and admin actions |

---

## GDPR Compliance

| Principle | Implementation |
|-----------|----------------|
| Data Minimization | Only essential data collected (name, email, message, IP) |
| Purpose Limitation | Data used exclusively for contact handling and abuse prevention |
| Storage Limitation | Automated IP anonymization after 14 days via cronjob |
| Integrity & Confidentiality | TLS transport, secure storage, access controls |
| Accountability | Audit trails for anonymization, login attempts, admin actions |
| Right to Erasure | Manual deletion on request; automated anonymization as baseline |

---

## Reporting a Vulnerability

If you discover a security vulnerability, please report it responsibly.

### Do

1. **Report privately** via one of these methods:
   - GitHub Security Advisory (preferred)
   - Encrypted contact form at the project's website

2. **Include in your report:**
   - Description of the vulnerability
   - Type (OWASP / CWE category if known)
   - Steps to reproduce
   - Affected versions and files
   - Potential impact assessment
   - Suggested fix (optional)

### Do Not

- Open a public GitHub issue for security findings
- Disclose the vulnerability before it has been patched
- Test against production systems without explicit permission

### Report Template

```markdown
## Vulnerability Report

**Type:** [e.g., XSS, CSRF, Authentication Bypass]
**Severity:** [Critical / High / Medium / Low]
**Affected Versions:** [e.g., 5.0.x]
**Affected Component:** [e.g., form handler, dashboard API]

### Description
[Clear description]

### Steps to Reproduce
1. [Step 1]
2. [Step 2]

### Impact
[What an attacker could achieve]

### Suggested Fix
[If you have recommendations]
```

### Response Timeline

| Severity | Initial Response | Patch Target | Coordinated Disclosure |
|----------|-----------------|--------------|------------------------|
| Critical | 24 hours | 7 days | After patch + 14 days |
| High | 48 hours | 14 days | After patch + 30 days |
| Medium | 72 hours | 30 days | After patch + 60 days |
| Low | 1 week | 60 days | After patch + 90 days |

---

## Security Best Practices for Deployment

### Required Configuration

```bash
# Generate secrets
openssl rand -base64 32    # → DASHBOARD_SECRET
php -r "echo password_hash('YourPassword', PASSWORD_ARGON2ID);"  # → DASHBOARD_PASSWORD_HASH

# Set permissions
chmod 600 assets/php/.env.prod
chmod 755 assets/php/logs/ assets/php/data/
```

### Recommended Server Headers

```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "DENY"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
```

### Monitoring Checklist

- Monitor failed login attempts in `logs/security.log`
- Review CSRF validation failures periodically
- Run `composer audit` after dependency updates
- Re-run `security-tests.sh` after any deployment
- Check DMARC aggregate reports for email spoofing attempts

### DNS Authentication

Ensure these records are configured for your domain:

- **SPF:** `v=spf1 a mx ~all` (at minimum)
- **DKIM:** Validate selector with your mail provider
- **DMARC:** `v=DMARC1; p=quarantine; pct=100; rua=mailto:postmaster@yourdomain.com`

Upgrade DMARC policy to `reject` after a monitoring period confirms no legitimate mail is affected.

---

## Known Design Decisions

These are intentional architectural choices, not vulnerabilities:

**Single admin account** — The dashboard uses a single password (no usernames) with Argon2id hashing. This is appropriate for the project's scope (personal portfolio site). Multi-user support is not planned.

**JSON file storage** — Blocklist, whitelist, and rate limiting data are stored in JSON files rather than a database. This keeps the system dependency-free and portable. File locking (`LOCK_EX`) prevents corruption from concurrent writes.

**Stateless authentication** — HMAC tokens stored in cookies replace server-side sessions for dashboard auth. This avoids session storage overhead and works without sticky sessions. The trade-off is that token revocation requires waiting for expiry (4 hours).

**Migration mode** — The CSRF and captcha protections for the contact form run in a backward-compatible migration mode until the frontend is updated to use the init endpoint. This means CSRF is only enforced when a token is present in the session. After frontend deployment, strict mode should be activated (see `MIGRATION.md`).

### Out of Scope

These items are intentionally not addressed:

- **DDoS protection** — Use a CDN/WAF like Cloudflare
- **Database encryption** — No database is used
- **Two-factor authentication** — Considered for a future version
- **Password reset flow** — Admin access recovery is manual by design

---

## Version History

| Version | Date | Security Scope |
|---------|------|----------------|
| 5.1.0 | 2026-03-27 | Phase 7: 3-layer disposable email detection (prefix/TLD scoring, 72k+ domain blacklist from [disposable-email-domains](https://github.com/disposable-email-domains/disposable-email-domains) (CC0), [DeBounce](https://disposable.debounce.io/) API check), `.htaccess` v2.1.0 (.txt file protection), security-tests.sh v2.0.0 (43 tests), contact-form-logic.js subpage path fix |
| 5.0.0 | 2026-03-25 | Comprehensive hardening: 14 findings, 6 phases, 31 automated tests |
| 4.2.0 | 2025-10-05 | Dashboard CSRF protection (Double Submit Cookie + JWT binding) |
| 4.1.0 | 2025-10-05 | Dashboard API authentication, CORS, PII masking |
| 4.0.0 | 2025-10-04 | Domain blacklist, Post-Redirect-Get pattern |
| 3.0.0 | 2025-09 | HMAC authentication, automated log anonymization |

See [CHANGELOG.md](CHANGELOG.md) for the complete version history including non-security changes.

---

**Last Updated:** March 2026  
**Security Status:** 🟢 Hardened (v5.1.0 — 14/14 findings addressed + Phase 7 spam filtering, 43 tests passing)  
**Next Review:** April 2026 (DMARC policy upgrade, frontend CSRF integration)
