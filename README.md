# Advanced Contact Form with Abuse Prevention

A production-ready, GDPR-compliant contact form system with comprehensive spam protection, extended logging, IP blocklist management, domain blacklist, and automated anonymization.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![GDPR Compliant](https://img.shields.io/badge/GDPR-Compliant-success)](https://gdpr.eu/)
[![Production Ready](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)](/)

---

## Table of Contents

- [Features](#features)
- [System Architecture](#system-architecture)
- [File Structure](#file-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Security Features](#security-features)
- [GDPR Compliance](#gdpr-compliance)
- [Dashboard Features](#dashboard-features)
- [Domain Blacklist](#domain-blacklist)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [About the Author](#about-the-author)
- [License](#license)

---

## Features

### 🔐 Core Functionality
- **PHPMailer Integration** - Reliable SMTP email delivery with TLS/SSL encryption
- **Server-Side Captcha** - Simple arithmetic challenge without third-party services
- **Multi-Layer Validation** - Comprehensive form field validation and sanitization
- **Honeypot Protection** - Hidden fields to trap automated bots
- **PRG Pattern** - Post-Redirect-Get prevents form resubmission errors

### 🛡️ Advanced Abuse Prevention
- **Extended Logging System** - GDPR-compliant logging with automatic anonymization
- **IP Blocklist/Whitelist** - Manual and automated IP blocking with expiration dates
- **Domain Blacklist** - Block disposable and spam email domains (NEW in v4.0)
- **Rate Limiting** - Prevent abuse through submission frequency controls
- **Spam Score Calculation** - Multi-factor spam detection (0-100 scale)
- **Pattern Detection** - Identifies suspicious content, links, and behaviors
- **Browser Fingerprinting** - Non-invasive technical identifier for duplicate detection

### 🔒 Security & Privacy
- **HMAC Token Authentication** - Stateless, cryptographically secure dashboard access
- **Automatic IP Anonymization** - Full IP addresses anonymized after 14 days
- **GDPR-Compliant Data Handling** - Complies with EU data protection regulations
- **Secure Cookie Handling** - HttpOnly, Secure, SameSite=Strict flags
- **Input Sanitization** - Protection against XSS, SQL injection, email injection
- **No Browser Storage APIs** - Secure implementation without localStorage/sessionStorage

### 📊 Management Dashboard (V2.0)
- **Real-Time Analytics** - Submission statistics, spam scores, trends
- **7-Day Trend Visualization** - Chart.js-powered analytics
- **Improved UX** - Clear status indicators (Submission Status vs IP Status)
- **Block Duration Display** - Shows expiration time for temporary blocks
- **Blocklist Statistics** - Active blocks, permanent blocks, expired entries
- **Block Reasons Analytics** - Track why submissions are blocked
- **Recent Submissions View** - Monitor last 50 non-anonymized submissions
- **One-Click Blocking** - Block IPs directly from submission logs with custom duration

---

## System Architecture

```
Contact Form Submission
         ↓
┌─────────────────────────────┐
│  Priority Check: Blocklist  │
│  - IP Blacklist             │
│  - IP Whitelist             │
└──────────┬──────────────────┘
           ↓
┌─────────────────────────────┐
│  Security Checks            │
│  - Honeypot                 │
│  - Rate Limit               │
│  - Captcha                  │
│  - Timestamp                │
└──────────┬──────────────────┘
           ↓
┌─────────────────────────────┐
│  Validation                 │
│  - Required Fields          │
│  - Email Format             │
│  - Domain Blacklist ⭐NEW   │
│  - Content Analysis         │
└──────────┬──────────────────┘
           ↓
┌─────────────────────────────┐
│  Spam Score Calculation     │
│  - Keywords (+5 each)       │
│  - Links (+5 each)          │
│  - Patterns (+10 each)      │
│  - Domain Block (+50) ⭐NEW │
│  - Rate Limit (+30)         │
└──────────┬──────────────────┘
           ↓
    Score >= 30?
         /    \
       YES     NO
        ↓      ↓
    BLOCK   ALLOW
        ↓      ↓
┌─────────────────────────────┐
│  Extended Logger            │
│  - Submission Details       │
│  - User-Agent               │
│  - Browser Fingerprint      │
│  - Spam Score & Reasons     │
└──────────┬──────────────────┘
           ↓
┌─────────────────────────────┐
│  PHPMailer                  │
│  - Admin Notification       │
│  - User Confirmation        │
│  - .eml Backup              │
└──────────┬──────────────────┘
           ↓
┌─────────────────────────────┐
│  Auto-Anonymization         │
│  - After 14 days            │
│  - IP: 192.168.1.100 → XXX  │
│  - Audit Trail Logged       │
└─────────────────────────────┘
```

---

## File Structure

```
contact-form-abuse-prevention/
│
├── assets/
│   ├── php/
│   │   ├── contact-php-handler.php          # Main form handler
│   │   ├── ContactFormValidator-v2.php      # Validation engine (v2.1)
│   │   ├── ExtendedLogger.php               # GDPR-compliant logging
│   │   ├── BlocklistManager.php             # IP blocklist management
│   │   ├── .env.prod                        # Configuration (not in repo)
│   │   │
│   │   ├── dashboard.php                    # Unified dashboard V2.0
│   │   ├── dashboard-login.php              # HMAC authentication
│   │   ├── dashboard-api.php                # JSON API endpoint
│   │   │
│   │   ├── logs/                            # Auto-created directory
│   │   │   ├── detailed_submissions.log     # Extended logs
│   │   │   ├── anonymization_history.log    # Audit trail
│   │   │   └── sent-eml/                    # Email backups
│   │   │
│   │   └── data/                            # Auto-created directory
│   │       ├── blocklist.json               # Blocked IPs with metadata
│   │       ├── whitelist.json               # Trusted IPs
│   │       └── domain-blacklist.txt         # Blocked email domains ⭐NEW
│   │
│   ├── css/
│   │   └── contact-form.css                 # Form styling
│   │
│   └── js/
│       ├── contact-form.js                  # Client-side validation
│       └── chart.min.js                     # Dashboard charts
│
├── vendor/                                   # Composer dependencies
│   └── phpmailer/phpmailer/                 # PHPMailer library
│
├── documentation/
│   ├── HMAC-AUTHENTICATION.md               # HMAC auth guide
│   ├── CONFIGURATION.md                     # Setup guide
│   ├── DEPLOYMENT-COMPLETE.md               # Deployment checklist
│   ├── SECURITY-AUDIT.md                    # Security review
│   └── INDEX.md                             # Documentation index
│
├── .htaccess                                # Apache configuration
├── .env.prod.example                        # Environment template
├── composer.json                            # Composer dependencies
├── privacy-contact-form.html                # Privacy policy (updated)
├── README.md                                # This file
└── index.html                               # Documentation viewer
```

---

## Installation

### Prerequisites

- PHP 7.4 or higher
- Apache/Nginx web server
- Composer (for PHPMailer)
- HTTPS enabled (required for secure cookies)
- SMTP mail server credentials

### Quick Start

```bash
# 1. Clone repository
git clone https://github.com/yourusername/contact-form-abuse-prevention.git
cd contact-form-abuse-prevention

# 2. Install dependencies
composer install

# 3. Configure environment
cp .env.prod.example assets/php/.env.prod
nano assets/php/.env.prod  # Edit SMTP and dashboard credentials

# 4. Generate dashboard secret
openssl rand -base64 32  # Copy to DASHBOARD_SECRET

# 5. Set permissions
chmod 755 assets/php/{logs,data}
chmod 600 assets/php/.env.prod

# 6. Test installation
php -l assets/php/contact-php-handler.php
```

### Environment Configuration

Edit `assets/php/.env.prod`:

```bash
# SMTP Configuration
SMTP_HOST=mail.yourdomain.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=noreply@yourdomain.com
SMTP_PASS=your-smtp-password

# Email Settings
RECIPIENT_EMAIL=admin@yourdomain.com

# Dashboard Authentication
DASHBOARD_PASSWORD=your-secure-password
DASHBOARD_SECRET=generate-with-openssl-rand-base64-32
```

---

## Configuration

### Form Validator Settings

Edit `ContactFormValidator-v2.php` or configure in handler:

```php
$validator = new ContactFormValidator([
    'blockThreshold' => 30,          // Spam score to block (0-100)
    'minSubmitTime' => 3,            // Minimum seconds to fill form
    'maxSubmitTime' => 3600,         // Maximum seconds before expiry
    'rateLimitMax' => 5,             // Max submissions per hour
    'rateLimitWindow' => 3600,       // Rate limit window (seconds)
    'maxLinks' => 3,                 // Max links in message
    'maxMessageLength' => 5000,      // Max message characters
    'domainBlacklistFile' => 'domain-blacklist.txt'  // ⭐NEW
]);
```

### Domain Blacklist ⭐ NEW Feature

Block email domains by editing `assets/php/data/domain-blacklist.txt`:

```
# Domain Blacklist for Contact Form
# One domain per line, case-insensitive
# Lines starting with # are comments

# Disposable Email Services
tempmail.com
guerrillamail.com
10minutemail.com
mailinator.com

# Your custom blocked domains
spam-domain.com
unwanted-site.net
```

**Features:**
- Auto-loaded on each validation
- Case-insensitive matching
- Comments supported (# prefix)
- No code changes needed
- Spam score: +50 points for blocked domains

### Spam Detection Tuning

Fine-tune spam keywords in `ContactFormValidator-v2.php`:

```php
'spamKeywords' => [
    'viagra', 'cialis', 'casino', 'lottery', 'prize',
    'click here', 'buy now', 'limited time', 'act now'
]
```

---

## Usage

### Contact Form Integration

**HTML Structure:**

```html
<form id="contactForm" method="POST" action="assets/php/contact-php-handler.php">
    <!-- Honeypot (hidden field) -->
    <input type="hidden" name="website" value="">
    
    <!-- Timestamp for timing check -->
    <input type="hidden" name="form_timestamp" id="form_timestamp">
    
    <!-- Required fields -->
    <input type="text" name="firstName" required>
    <input type="text" name="lastName" required>
    <input type="email" name="email" required>
    <textarea name="message" required></textarea>
    
    <!-- Captcha -->
    <label>
        <span id="captchaQuestion"></span>
        <input type="text" name="captchaAnswer" required>
    </label>
    
    <!-- Privacy checkbox -->
    <label>
        <input type="checkbox" name="privacy" required>
        I accept the <a href="privacy-contact-form.html">privacy policy</a>
    </label>
    
    <button type="submit">Send</button>
</form>

<script src="assets/js/contact-form.js"></script>
```

### Dashboard Access

**Login Flow:**
1. Navigate to: `https://yourdomain.com/assets/php/dashboard-login.php`
2. Enter password (from `.env.prod`)
3. Receive 24-hour HMAC token (stored in secure cookie)
4. Access dashboard

**Dashboard Tabs:**

#### 1. Overview
- **Today's Statistics**: Total, allowed, blocked submissions
- **Spam Score Average**: Overall spam score trend
- **Blocklist Stats**: Total entries, permanent blocks
- **7-Day Trend Chart**: Visual submission history
- **Top IPs**: Most frequent submitters
- **Block Reasons**: Why submissions were blocked ⭐NEW

#### 2. Recent Submissions
- **Last 50 Non-Anonymized Logs** (GDPR: 14 days)
- **Improved Status Display** ⭐NEW:
  - **Submission Status**: Allowed ✓ or Blocked 🚫
  - **IP Status**: Already Blocked, Whitelisted, or [Block IP] button
- **Block Duration Display** ⭐NEW: Shows expiration time
- **Spam Score Badge**: Color-coded (green/yellow/red)
- **One-Click Blocking**: With custom duration (1-90 days, permanent)

#### 3. Blocklist
- **Active Blocks**: Current blocked IPs
- **Expiration Management**: Automatic cleanup of expired blocks
- **Block Metadata**: Reason, added date, expires date
- **Quick Actions**: Unblock button

#### 4. Whitelist
- **Trusted IPs**: Never blocked
- **Add New**: Manual whitelist with note
- **Remove**: Quick whitelist removal

---

## Security Features

### 1. HMAC Token Authentication

**No PHP Sessions** - Stateless authentication:

```
Token Structure: [BASE64_PAYLOAD].[HMAC_SIGNATURE]

Payload: {"user": "dashboard_admin", "exp": 1730123456, "iat": 1730037056}
Signature: HMAC-SHA256(payload, DASHBOARD_SECRET)
```

**Benefits:**
- ✅ No session storage
- ✅ Cannot be forged
- ✅ Automatic expiration
- ✅ Resistant to session hijacking
- ✅ Horizontal scaling friendly

### 2. Multi-Layer Spam Detection

| Check | Score | Triggered When | NEW |
|-------|-------|----------------|-----|
| IP Blocklisted | +100 | Manual block | |
| Blocked Domain | +50 | Email from blacklist | ⭐ |
| Honeypot filled | +50 | Bot filled hidden field | |
| Submitted too fast | +40 | <3 seconds | |
| Rate limit exceeded | +30 | >5/hour from IP | |
| Missing fields | +20 | Required field empty | |
| Spam keywords | +5 each | Trigger words found | |
| Excessive links | +5 each | >3 URLs | |
| Suspicious patterns | +10 each | Regex matches | |

**Threshold: Score >= 30 → BLOCKED**

### 3. Input Sanitization

All inputs pass through multi-stage sanitization:

```php
function sanitize_text(string $input): string {
    $input = trim($input);
    $input = str_replace(["\r", "\n", "\0"], ' ', $input);
    $input = filter_var($input, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}
```

**Prevents:**
- XSS (Cross-Site Scripting)
- SQL Injection
- Email Header Injection
- CRLF Injection
- NULL byte attacks

### 4. Secure Cookies

```php
setcookie('dashboard_token', $token, [
    'expires' => time() + 86400,
    'path' => '/assets/php/',
    'secure' => true,        // HTTPS only
    'httponly' => true,      // No JavaScript access
    'samesite' => 'Strict'   // CSRF protection
]);
```

### 5. PRG Pattern ⭐ NEW

**Post-Redirect-Get** prevents form resubmission errors:

```php
// After POST action
header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=success');
exit;
```

**Benefits:**
- ✅ No "Form resubmission" browser warnings
- ✅ Clean URLs after actions
- ✅ Better user experience

---

## GDPR Compliance

### Data Minimization

**Only Essential Data Collected:**
- Name, email (for response)
- Message content (for inquiry)
- IP address (security - **14 days only**)
- Technical metadata (spam detection)

### Automatic Anonymization ⭐

**IP Addresses Anonymized After 14 Days:**

```
BEFORE (Day 1-14):
192.168.1.100
2001:db8::1

AFTER (Day 15+):
192.168.1.XXX
2001:db8::XXX
```

**Process:**
1. Cron job runs on every dashboard access
2. Scans logs older than 14 days
3. Replaces last IP segment irreversibly
4. Logs action in `anonymization_history.log`

**Audit Trail:**
```json
{
  "timestamp": "2025-10-18T10:00:00Z",
  "action": "anonymized",
  "count": 15,
  "details": "Anonymized 15 entries older than 14 days"
}
```

### User Rights Support

| Right | Implementation |
|-------|----------------|
| **Access (Art. 15)** | Dashboard search + JSON export |
| **Rectification (Art. 16)** | Manual log editing |
| **Erasure (Art. 17)** | Delete button + automatic after 14 days |
| **Restriction (Art. 18)** | Whitelist feature |
| **Portability (Art. 20)** | JSON log format |
| **Object (Art. 21)** | Unblock + whitelist |

### Legal Basis

- **Art. 6(1)(b) GDPR** - Contract performance (inquiry response)
- **Art. 6(1)(a) GDPR** - Consent (privacy checkbox)
- **Art. 6(1)(f) GDPR** - Legitimate interest (spam protection, security)

### Privacy Policy

Comprehensive GDPR-compliant policy: [privacy-contact-form.html](privacy-contact-form.html)

**Includes:**
- Extended logging details
- Blocklist/whitelist explanation
- Cookie usage (HMAC token)
- Anonymization process
- User rights
- Contact for data requests

---

## Dashboard Features (V2.0) ⭐

### Improved UX

**Clear Status Separation:**
- **Submission Status**: Whether THIS submission was blocked
- **IP Status**: Current state of the IP address

**Before (confusing):**
```
Status: Allowed | Actions: Blocked
```

**After V2.0 (clear):**
```
Submission Status: ✓ Allowed (green)
IP Status: 🚫 Already Blocked (gray) + Expires: 2025-11-04 14:23
```

### Block Duration Display

Shows when temporary blocks expire:

```
Already Blocked
Expires: 2025-10-25 18:30
```

or

```
Already Blocked
Permanent
```

### Blocklist Statistics

**New Overview Cards:**
- **Blocklist Entries Total**: Active blocks count
- **Permanent Blocks**: Number of permanent entries
- **Block Reasons from Submissions**: Why forms were blocked

### PRG Pattern Implementation

**Problem Solved:**
After blocking an IP, page refresh caused "Form resubmission" warning.

**Solution:**
All POST actions now redirect to GET with success message:
```
POST → Redirect (302) → GET + ?msg=success&type=success
```

---

## Domain Blacklist ⭐ NEW

### Overview

Block submissions from disposable or spam email domains without code changes.

### Setup

1. **File Location**: `assets/php/data/domain-blacklist.txt`
2. **Format**: One domain per line, `#` for comments
3. **Auto-reload**: Loaded on every form submission

### Example Configuration

```
# Disposable Email Services
tempmail.com
guerrillamail.com
10minutemail.com
mailinator.com
throwaway.email

# Custom Spam Domains
spam-domain.com
unwanted-site.net
```

### How It Works

1. User submits form with email `user@tempmail.com`
2. Validator extracts domain: `tempmail.com`
3. Checks against blacklist (case-insensitive)
4. **Match found**: +50 spam score
5. If total score >= 30: **BLOCKED**

### Benefits

- ✅ No code changes required
- ✅ Easy to maintain
- ✅ Case-insensitive matching
- ✅ Comments supported
- ✅ Instant activation

### Future Extensions

**Possible additions:**
- TLD blacklist (`.ru`, `.cn`, etc.)
- Regex patterns (`*.tempmail.*`)
- API integration (disposable email detection)
- Dashboard UI for management

---

## Troubleshooting

### Dashboard Issues

**Problem: "Erneute Formular-Übermittlung bestätigen"**

✅ **Fixed in V2.0** - PRG Pattern prevents this

**Problem: Dashboard shows wrong stats**

Check:
```bash
# Validate JSON logs
php -r 'json_decode(file_get_contents("assets/php/logs/detailed_submissions.log"));'

# Check API endpoint
curl https://yourdomain.com/assets/php/dashboard-api.php
```

**Problem: IP not blocking**

Verify blocklist:
```bash
cat assets/php/data/blocklist.json
# Should be valid JSON with IP entries
```

### Email Issues

**Problem: Emails not sending**

Check:
1. SMTP credentials in `.env.prod`
2. Port (587=TLS, 465=SSL)
3. Firewall allows outbound SMTP
4. PHPMailer debug mode:
   ```php
   $mail->SMTPDebug = 2;  // Enable verbose debugging
   ```

### Permission Issues

**Problem: Logs not created**

```bash
# Fix permissions
chmod 755 assets/php/logs
chmod 755 assets/php/data
chown www-data:www-data assets/php/{logs,data}
```

### Domain Blacklist Issues

**Problem: Domain blocking not working**

Debug:
```php
$validator = new ContactFormValidator();
var_dump($validator->getDomainBlacklist());
// Should show array of domains
```

Check:
1. File exists: `assets/php/data/domain-blacklist.txt`
2. File readable: `chmod 644 domain-blacklist.txt`
3. Correct format: one domain per line, no spaces

---

## Contributing

Contributions are welcome! This project follows open-source best practices and aims to maintain high code quality and security standards.

### How to Contribute

1. **Fork** the repository
2. **Create feature branch**: `git checkout -b feature/AmazingFeature`
3. **Commit changes**: `git commit -m 'Add AmazingFeature'`
4. **Push to branch**: `git push origin feature/AmazingFeature`
5. **Open Pull Request**

### Contribution Guidelines

#### Code Standards

- Follow **PSR-12** coding standards for PHP
- Add **PHPDoc** comments for all public methods
- Maintain **backward compatibility** when possible
- Write **clear commit messages** following conventional commits format

#### Security

- Never commit sensitive data (passwords, API keys, tokens)
- Report security vulnerabilities privately (see Security Disclosures below)
- All user input must be sanitized and validated
- Follow OWASP Top 10 security guidelines

#### Documentation

- Update documentation for any new features
- Include code examples where applicable
- Add entries to CHANGELOG.md
- Update README.md if functionality changes

#### Testing

Before submitting a PR, ensure:

- [ ] PHP syntax check passes: `php -l file.php`
- [ ] Form submission test (successful)
- [ ] Form submission test (blocked)
- [ ] Dashboard login test
- [ ] Blocklist add/remove test
- [ ] Domain blacklist test
- [ ] Log files created correctly
- [ ] No PHP errors in logs

### Code Review Process

1. **Automated checks** run on all PRs
2. **Maintainer review** (typically within 48 hours)
3. **Feedback and iteration** if needed
4. **Merge** once approved

### Types of Contributions Welcome

- 🐛 Bug fixes
- ✨ New features (discuss in an issue first)
- 📚 Documentation improvements
- 🎨 UI/UX enhancements
- 🔒 Security improvements
- 🌍 Translations (future feature)
- ⚡ Performance optimizations

### First-Time Contributors

Look for issues tagged with `good first issue` or `help wanted`. These are great starting points for newcomers to the project.

### Questions?

- Open an issue with the `question` label
- Check existing issues and discussions first
- Be respectful and constructive

### Security Disclosures

Found a security vulnerability? **Please report it privately:**

1. **DO NOT** open a public issue
2. Email details trough contact form at https://jozapf.de (or create a private security advisory on GitHub)
3. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

We aim to respond within 48 hours and will credit you in the security advisory once patched.

---

## About the Author

This project was developed by **Jo Zapf**, an IT apprentice specializing in application development, as part of a comprehensive learning journey in secure web application development.

### Background

What started as a simple contact form quickly evolved into a production-ready security system when faced with real-world spam attacks. Rather than using off-the-shelf solutions, I chose to build a custom system from the ground up to truly understand the underlying security principles.

### Key Learning Areas

Throughout this project, I gained hands-on experience with:

- **Security Architecture**: HMAC authentication, input sanitization, abuse prevention
- **GDPR Compliance**: Data minimization, automatic anonymization, privacy-by-design
- **Full-Stack Development**: PHP backend, JavaScript frontend, RESTful APIs
- **Database Design**: JSON-based logging, efficient data structures
- **DevOps**: Composer dependencies, deployment strategies, monitoring

### Development Approach

This project follows professional software engineering practices:

- ✅ Modular, reusable code architecture
- ✅ Comprehensive documentation
- ✅ Security-first design principles
- ✅ GDPR compliance from day one
- ✅ Production-tested and battle-proven

### Philosophy

*"Security isn't a feature you add later—it's a foundation you build upon."*

This project embodies that philosophy, treating security and privacy as core requirements rather than afterthoughts.

---

**Portfolio**: [jozapf.de](https://jozapf.de)  
**GitHub**: [@jozapf](https://github.com/jozapf)  
**LinkedIn**: [Jo Zapf](https://www.linkedin.com/in/jozapf)

---

## License

This project is licensed under the **MIT License** - see below for details.

### MIT License

```
MIT License

Copyright (c) 2025 Jo Zapf

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
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

### What This Means

**You are free to:**
- ✅ Use this software commercially
- ✅ Modify the source code
- ✅ Distribute copies
- ✅ Use it privately
- ✅ Use it for any purpose

**Under the conditions:**
- 📄 Include the original license and copyright notice
- 📄 State any significant changes made

**Limitations:**
- ❌ No warranty provided
- ❌ Author is not liable for any damages
- ❌ No trademark rights granted

### Why MIT License?

The MIT License was chosen for this project because:

1. **Simple and Clear**: Easy to understand for developers and legal teams
2. **Permissive**: Allows maximum freedom for users and contributors
3. **Industry Standard**: Most widely-used open-source license
4. **Business-Friendly**: Companies can use it without concerns
5. **Community-Proven**: Used by React, Node.js, Bootstrap, and thousands more

### Attribution

If you use this project, attribution is appreciated but not required. A link back to this repository or a mention in your credits would be awesome! 🙏

**Example attribution:**
```
Contact form security powered by Advanced Contact Form with Abuse Prevention
(https://github.com/JoZapf/contact-form-abuse-prevention)
```

---

## Acknowledgments

Special thanks to:

- **PHPMailer Team** - For the excellent SMTP library
- **Chart.js Team** - For beautiful dashboard visualizations
- **Open Source Community** - For inspiration and best practices
- **Beta Testers** - For valuable feedback and bug reports

---

## Changelog

### Version 4.0.0 (2025-10-04)

**New Features:**
- ⭐ Domain blacklist support
- ⭐ PRG Pattern implementation
- ⭐ Dashboard V2.0 with improved UX
- ⭐ Block duration display
- ⭐ Blocklist statistics in overview

**Improvements:**
- Better status indicators (Submission vs IP status)
- Clear expiration time display
- Block reasons analytics
- Enhanced documentation

**Bug Fixes:**
- Fixed "form resubmission" warning
- Fixed dashboard logout issues
- Improved cookie security

### Version 3.0.0

- HMAC authentication
- Extended logging
- IP anonymization
- Rate limiting

### Version 2.0.0

- Blocklist/Whitelist management
- Dashboard implementation
- Spam score calculation

### Version 1.0.0

- Initial release
- Basic contact form
- PHPMailer integration

---

## Project Status

| Metric | Status |
|--------|--------|
| **Version** | 4.0.0 |
| **Status** | ✅ Production Ready |
| **Last Updated** | October 2025 |
| **Maintenance** | 🟢 Active |
| **PHP Version** | ≥7.4 |
| **GDPR Compliant** | ✅ Yes |
| **Test Coverage** | Manual Testing |

### Roadmap

**Planned Features:**
- [ ] Advanced bot detection (User-Agent analysis)
- [ ] Email verification API integration
- [ ] Multi-language support
- [ ] WebAuthn 2FA for dashboard
- [ ] REST API for external integrations

**Under Consideration:**
- GeoIP location detection
- Machine learning spam detection
- Automated penetration testing
- Docker containerization

---

## Support

### Resources

- **Documentation**: [/documentation/](documentation/)
- **Issues**: [GitHub Issues](https://github.com/yourusername/contact-form-abuse-prevention/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/contact-form-abuse-prevention/discussions)

### Getting Help

1. Check [Troubleshooting](#troubleshooting) section
2. Review [documentation](documentation/)
3. Search [existing issues](https://github.com/yourusername/contact-form-abuse-prevention/issues)
4. Open a new issue with:
   - PHP version
   - Error messages
   - Steps to reproduce
   - Expected vs actual behavior

---

## Statistics

**Lines of Code:** ~3,500  
**Files:** 15+  
**Dependencies:** 1 (PHPMailer)  
**Test Coverage:** Manual  
**Documentation Pages:** 8  

---

**Made with ❤️ for secure, GDPR-compliant contact forms**

**Star ⭐ this repo if you find it useful!**
