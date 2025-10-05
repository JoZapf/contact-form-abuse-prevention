# Contributing to Contact Form Abuse Prevention

Thank you for your interest in contributing to this project! We welcome contributions from the community and appreciate your help in making this contact form solution more secure and robust.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Project Structure](#project-structure)
- [Coding Standards](#coding-standards)
- [Testing Requirements](#testing-requirements)
- [Security Guidelines](#security-guidelines)
- [Commit Message Guidelines](#commit-message-guidelines)
- [Pull Request Process](#pull-request-process)
- [Code Review Criteria](#code-review-criteria)
- [Documentation Guidelines](#documentation-guidelines)
- [Release Process](#release-process)
- [Getting Help](#getting-help)

---

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors. We pledge to:

- Use welcoming and inclusive language
- Respect differing viewpoints and experiences
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

### Unacceptable Behavior

- Harassment, discrimination, or offensive comments
- Personal attacks or trolling
- Publishing others' private information
- Any conduct that would be inappropriate in a professional setting

### Enforcement

Violations of the code of conduct may be reported to the project maintainers. All complaints will be reviewed and investigated promptly and fairly.

---

## Getting Started

### Prerequisites

Before contributing, ensure you have:

- **PHP 7.4 or higher** installed
- **Composer** for dependency management
- **Git** for version control
- **Apache/Nginx** web server (for local testing)
- **OpenSSL** for generating secrets
- Basic understanding of:
  - PHP security best practices
  - OWASP Top 10 vulnerabilities
  - CSRF protection patterns
  - HMAC authentication

### Find an Issue

1. Check the [Issues](https://github.com/yourusername/contact-form-abuse-prevention/issues) page
2. Look for issues labeled:
   - `good first issue` - Beginner-friendly
   - `help wanted` - Community contributions welcome
   - `bug` - Bug fixes needed
   - `enhancement` - New features
   - `security` - Security improvements
3. Comment on the issue to let others know you're working on it

### Types of Contributions

We welcome:

- 🐛 **Bug fixes** - Fix broken functionality
- ✨ **Features** - Add new capabilities
- 🔒 **Security improvements** - Enhance security posture
- 📝 **Documentation** - Improve or add documentation
- 🧪 **Tests** - Add or improve test coverage
- 🎨 **UI/UX** - Improve dashboard interface
- ♻️ **Refactoring** - Improve code quality

---

## Development Setup

### 1. Fork and Clone

```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/JoZapf/contact-form-abuse-prevention.git
cd contact-form-abuse-prevention

# Add upstream remote
git remote add upstream https://github.com/JoZapf/contact-form-abuse-prevention.git
```

### 2. Install Dependencies

```bash
# Install Composer dependencies
composer install

# Verify installation
composer validate
```

### 3. Configure Environment

```bash
# Copy example environment file
cp assets/php/.env.prod.example assets/php/.env.prod

# Generate secrets
openssl rand -base64 32  # For DASHBOARD_SECRET

# Edit configuration
nano assets/php/.env.prod
```

**Required configuration:**
```bash
SMTP_HOST=mail.example.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=noreply@example.com
SMTP_PASS=your-smtp-password
RECIPIENT_EMAIL=admin@example.com
DASHBOARD_PASSWORD=your-secure-password
DASHBOARD_SECRET=generate-with-openssl
ALLOWED_ORIGIN="http://localhost:8080"  # For local dev
```

### 4. Set Permissions

```bash
chmod 755 assets/php/{logs,data}
chmod 600 assets/php/.env.prod
```

### 5. Start Development Server

```bash
# Using PHP built-in server
php -S localhost:8080 -t .

# OR using Apache/Nginx virtual host
# Configure your web server to point to project root
```

### 6. Verify Installation

```bash
# Test PHP syntax
find assets/php -name "*.php" -exec php -l {} \;

# Test contact form handler
php -l assets/php/contact-php-handler.php

# Test dashboard
php -l assets/php/dashboard.php
php -l assets/php/dashboard-api.php
php -l assets/php/dashboard-login.php
```

### 7. Create Feature Branch

```bash
# Update your fork
git fetch upstream
git checkout main
git merge upstream/main

# Create feature branch
git checkout -b feature/your-feature-name
# OR for bug fixes:
git checkout -b fix/issue-description
# OR for security fixes:
git checkout -b security/vulnerability-description
```

---

## Project Structure

```
contact-form-abuse-prevention/
│
├── assets/
│   ├── php/                          # Backend PHP files
│   │   ├── contact-php-handler.php   # Main form handler
│   │   ├── ContactFormValidator-v2.php  # Validation engine
│   │   ├── ExtendedLogger.php        # Logging system
│   │   ├── BlocklistManager.php      # IP management
│   │   ├── dashboard.php             # Admin dashboard
│   │   ├── dashboard-api.php         # JSON API
│   │   ├── dashboard-login.php       # Authentication
│   │   ├── .env.prod                 # Configuration (gitignored)
│   │   ├── logs/                     # Log files (gitignored)
│   │   └── data/                     # JSON data (gitignored)
│   │
│   ├── css/                          # Stylesheets
│   │   └── contact-form.css
│   │
│   └── js/                           # JavaScript
│       ├── contact-form-logic.js     # Client validation
│       └── chart.js                  # Dashboard charts
│
├── vendor/                           # Composer dependencies
│   └── phpmailer/phpmailer/
│
├── Documentation/                    # Project documentation
│   ├── COMPOSER-DEPENDENCIES.md
│   ├── HMAC-AUTHENTICATION.md
│   └── CSRF-PROTECTION.md
│
├── .gitignore                        # Git ignore rules
├── .env.prod.example                 # Environment template
├── composer.json                     # Dependencies
├── README.md                         # Main documentation
├── SECURITY.md                       # Security policy
├── CONTRIBUTING.md                   # This file
├── CHANGELOG.md                      # Version history
└── LICENSE                           # MIT License
```

---

## Coding Standards

### PHP Standards (PSR-12)

We follow **PSR-12: Extended Coding Style** for all PHP code.

#### File Structure

```php
<?php
/**
 * Brief file description
 * 
 * @version     X.Y.Z
 * @date        YYYY-MM-DD HH:MM:SS UTC
 * @package     ContactFormAbusePrevention
 * @author      Your Name
 */

// Namespace (if applicable)
namespace ContactForm;

// Use statements (alphabetically sorted)
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Constants
const MAX_ATTEMPTS = 5;

// Class definition
class ExampleClass {
    // Class implementation
}
```

#### Indentation and Spacing

```php
// ✅ Good: 4 spaces, no tabs
function exampleFunction($param1, $param2) {
    if ($param1 === $param2) {
        return true;
    }
    return false;
}

// ❌ Bad: Tabs or inconsistent spacing
function exampleFunction($param1,$param2){
	if($param1===$param2){return true;}
	return false;
}
```

#### Naming Conventions

```php
// Classes: PascalCase
class ContactFormValidator {}

// Functions/Methods: camelCase
function validateEmail($email) {}

// Variables: camelCase
$userName = 'admin';
$isValid = true;

// Constants: UPPER_SNAKE_CASE
const MAX_SPAM_SCORE = 30;
const CSRF_TOKEN_LENGTH = 32;

// Private properties: camelCase with underscore prefix
private $_secretKey;
```

#### Documentation (PHPDoc)

```php
/**
 * Validate CSRF token from POST request
 * 
 * Performs two-stage validation:
 * 1. Cookie value must match POST value (Double Submit Cookie)
 * 2. JWT claim must match Cookie value (Token binding)
 * 
 * @param string $token Dashboard JWT token
 * @param string $secret DASHBOARD_SECRET from .env.prod
 * @return bool True if valid, false otherwise
 * @throws InvalidArgumentException If token format is invalid
 * 
 * @since v2.1.0 (AP-02)
 * @see CSRF-PROTECTION.md for implementation details
 */
function validateCsrfToken($token, $secret) {
    // Implementation
}
```

#### Security Best Practices

```php
// ✅ Always sanitize user input
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');

// ✅ Use timing-safe comparison for tokens
if (hash_equals($expected, $actual)) {}

// ✅ Fail-fast pattern for configuration
$secret = env('DASHBOARD_SECRET');
if (!$secret) {
    http_response_code(500);
    die('Configuration error');
}

// ❌ Never hardcode credentials
$password = 'admin123';  // DON'T DO THIS

// ❌ Never use == for token comparison
if ($token == $_POST['token']) {}  // Vulnerable to timing attacks
```

### JavaScript Standards

```javascript
// Use const for constants
const MAX_RETRIES = 3;

// Use let for variables
let isLoading = false;

// Functions: camelCase
function loadDashboard() {
    // Implementation
}

// Avoid var (use let/const instead)
// ❌ Bad
var data = {};

// ✅ Good
const data = {};
```

### CSS Standards

```css
/* Use meaningful class names */
.dashboard-header { }
.stat-card { }

/* Use kebab-case for class names */
.csrf-token-field { }

/* Group related styles */
.btn {
    padding: 8px 16px;
    border-radius: 6px;
}

.btn-primary {
    background: #3498db;
    color: white;
}
```

---

## Testing Requirements

### Before Submitting a PR

All contributions must pass the following tests:

#### 1. PHP Syntax Check

```bash
# Check all PHP files
find assets/php -name "*.php" -exec php -l {} \;

# Should output: "No syntax errors detected"
```

#### 2. Functional Testing

**Contact Form Tests:**
```bash
# Test: Successful submission
curl -X POST http://localhost:8080/assets/php/contact-php-handler.php \
  -d "name=Test User" \
  -d "email=test@example.com" \
  -d "message=Test message" \
  -d "captcha_answer=correct"

# Expected: Success message

# Test: Spam detection
curl -X POST http://localhost:8080/assets/php/contact-php-handler.php \
  -d "name=Spammer" \
  -d "email=spam@tempmail.com" \
  -d "message=Buy cheap viagra! http://spam.com"

# Expected: Blocked message
```

**Dashboard Tests:**
```bash
# Test: Login
curl -X POST http://localhost:8080/assets/php/dashboard-login.php \
  -d "password=your-test-password" \
  -c cookies.txt

# Expected: Redirect to dashboard.php

# Test: CSRF protection
curl -X POST http://localhost:8080/assets/php/dashboard.php \
  -b cookies.txt \
  -d "action=block_ip&ip=192.168.1.1"

# Expected: HTTP 403 (missing CSRF token)

# Test: Valid CSRF submission
CSRF=$(grep csrf_token cookies.txt | awk '{print $7}')
curl -X POST http://localhost:8080/assets/php/dashboard.php \
  -b cookies.txt \
  -d "action=block_ip&ip=192.168.1.1&csrf_token=$CSRF&reason=test"

# Expected: HTTP 302 (success)
```

**API Tests:**
```bash
# Test: Unauthenticated access
curl -i http://localhost:8080/assets/php/dashboard-api.php

# Expected: HTTP 401 Unauthorized

# Test: Authenticated access
curl -i -b cookies.txt http://localhost:8080/assets/php/dashboard-api.php

# Expected: HTTP 200 with JSON data
```

#### 3. Security Testing

```bash
# Check for hardcoded credentials
grep -r "password\s*=\s*['\"]" assets/php/*.php
# Should return: nothing

# Check for SQL injection risks (we don't use SQL, but good practice)
grep -r "mysql_query\|mysqli_query" assets/php/*.php
# Should return: nothing

# Verify .env not committed
git ls-files | grep ".env.prod$"
# Should return: nothing
```

#### 4. Code Quality

```bash
# Check for PHP errors
php -d display_errors=1 assets/php/contact-php-handler.php

# Validate composer.json
composer validate

# Check dependencies for vulnerabilities
composer audit
```

### Test Checklist

Before submitting, verify:

- [ ] All PHP syntax checks pass
- [ ] Contact form submits successfully
- [ ] Spam detection blocks malicious submissions
- [ ] Dashboard login works
- [ ] CSRF protection blocks unauthorized requests
- [ ] API authentication works
- [ ] No hardcoded credentials in code
- [ ] No sensitive files committed (.env, logs, etc.)
- [ ] Composer dependencies are up to date
- [ ] No PHP errors in error logs
- [ ] Browser console shows no JavaScript errors

---

## Security Guidelines

### Critical Security Rules

#### 1. Never Commit Sensitive Data

**Always gitignored:**
- `.env.prod` - Configuration with credentials
- `assets/php/logs/` - Log files with IPs/emails
- `assets/php/data/` - Blocklist/whitelist data
- `vendor/` - Composer dependencies

**Check before commit:**
```bash
# Ensure .env not staged
git status | grep ".env"

# If found, remove from staging
git reset HEAD assets/php/.env.prod
```

#### 2. Input Sanitization

**All user input must be sanitized:**

```php
// ✅ Good
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// ❌ Bad
$name = $_POST['name'];  // Vulnerable to XSS
```

#### 3. CSRF Protection

**All state-changing operations must have CSRF tokens:**

```php
// ✅ Good: Validate CSRF before action
if (!validateCsrfToken($token, $secret)) {
    http_response_code(403);
    die('CSRF validation failed');
}
processAction($_POST['action']);

// ❌ Bad: Process without validation
processAction($_POST['action']);
```

#### 4. Secure Cookies

**Always use secure cookie flags:**

```php
// ✅ Good
setcookie('token', $value, [
    'secure' => true,      // HTTPS only
    'httponly' => true,    // No JS access
    'samesite' => 'Strict' // CSRF protection
]);

// ❌ Bad
setcookie('token', $value);  // Insecure
```

#### 5. Error Handling

**Never expose sensitive information in errors:**

```php
// ✅ Good
try {
    connectDatabase();
} catch (Exception $e) {
    error_log($e->getMessage());
    die('An error occurred. Please try again.');
}

// ❌ Bad
try {
    connectDatabase();
} catch (Exception $e) {
    die($e->getMessage());  // Exposes connection details
}
```

### Security Review Checklist

- [ ] No hardcoded credentials
- [ ] All user input sanitized
- [ ] CSRF tokens on state-changing operations
- [ ] Secure cookie flags used
- [ ] Timing-safe comparisons for tokens (`hash_equals()`)
- [ ] Fail-fast configuration (no silent defaults)
- [ ] Error messages don't expose sensitive info
- [ ] No SQL injection risks (we use JSON, but check anyway)
- [ ] XSS prevention (`htmlspecialchars()` on output)
- [ ] HTTPS enforced in production

---

## Commit Message Guidelines

We follow **Conventional Commits** specification.

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat` - New feature
- `fix` - Bug fix
- `security` - Security improvement
- `docs` - Documentation changes
- `style` - Code style changes (formatting, no logic change)
- `refactor` - Code refactoring
- `test` - Adding or updating tests
- `chore` - Maintenance tasks

### Scopes

- `dashboard` - Dashboard UI/logic
- `api` - Dashboard API
- `form` - Contact form handler
- `validator` - Form validation
- `logger` - Logging system
- `blocklist` - Blocklist management
- `auth` - Authentication system
- `docs` - Documentation
- `deps` - Dependencies

### Examples

**Good commit messages:**

```bash
# New feature
feat(dashboard): add block duration selector to IP blocking modal

Allows admins to select custom block duration (1/7/30/90 days or permanent)
when blocking IPs from the submissions table.

Closes #42

# Security fix
security(api): implement AP-02 CSRF protection for admin actions

- Add Double Submit Cookie pattern with JWT token binding
- Protect all 4 admin POST actions (block/unblock/whitelist)
- Generate 32-byte cryptographically secure CSRF tokens
- Validate tokens with timing-safe comparison
- Return HTTP 403 on failed validation

Risk reduction: ~90% for CSRF attacks
Closes: AP-02

# Bug fix
fix(validator): correct domain blacklist case-sensitivity check

Domain blacklist was case-sensitive, allowing spam@TempMail.com
to bypass spam@tempmail.com block. Now uses strtolower().

Fixes #38

# Documentation
docs: add CONTRIBUTING.md with development setup guide

Includes:
- Development environment setup
- Coding standards (PSR-12)
- Testing requirements
- Security guidelines
- PR process

# Refactoring
refactor(logger): extract IP anonymization to separate method

Split anonymizeSubmissions() into smaller, testable methods:
- shouldAnonymize()
- anonymizeIP()
- logAnonymization()

No functional changes.
```

**Bad commit messages:**

```bash
# Too vague
fix: bug fix

# No scope
add new feature

# Not descriptive
update files

# Missing body for complex change
feat: add CSRF protection
```

### Commit Message Checklist

- [ ] Type is one of: feat/fix/security/docs/style/refactor/test/chore
- [ ] Scope clearly identifies affected component
- [ ] Subject is imperative mood ("add" not "added")
- [ ] Subject is under 72 characters
- [ ] Body explains what and why (not how)
- [ ] Footer includes issue references (Closes #X, Fixes #Y)

---

## Pull Request Process

### 1. Ensure All Tests Pass

Before opening a PR, verify:

```bash
# Run full test suite
./scripts/run-tests.sh  # If available

# OR run tests manually:
# - PHP syntax check
# - Functional tests
# - Security checks
# - Code quality validation
```

### 2. Update Documentation

- Update `README.md` if functionality changed
- Add entry to `CHANGELOG.md`
- Update relevant documentation in `Documentation/`
- Add PHPDoc comments to new functions

### 3. Create Pull Request

**Title format:**
```
<type>(<scope>): Brief description
```

**Description template:**
```markdown
## Description
Brief summary of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Security improvement
- [ ] Documentation update
- [ ] Refactoring

## Testing
- [ ] PHP syntax check passed
- [ ] Functional tests passed
- [ ] Security tests passed
- [ ] Manual testing completed

## Checklist
- [ ] Code follows PSR-12 standards
- [ ] PHPDoc comments added
- [ ] README.md updated (if needed)
- [ ] CHANGELOG.md updated
- [ ] No sensitive data committed
- [ ] All tests pass

## Related Issues
Closes #XX
Fixes #YY

## Screenshots (if applicable)
[Add screenshots here]

## Additional Notes
[Any additional context]
```

### 4. Code Review Process

**What reviewers look for:**

1. **Functionality**
   - Does it work as intended?
   - Are edge cases handled?
   - Is error handling robust?

2. **Security**
   - Input sanitized?
   - CSRF protection (if needed)?
   - No hardcoded credentials?
   - Secure cookie flags?

3. **Code Quality**
   - Follows PSR-12?
   - Well-documented?
   - No code duplication?
   - Readable and maintainable?

4. **Testing**
   - Tests included?
   - Tests pass?
   - Coverage adequate?

5. **Documentation**
   - README updated?
   - CHANGELOG updated?
   - Comments clear?

### 5. Responding to Feedback

- Address all review comments
- Push additional commits (don't force push)
- Mark conversations as resolved when addressed
- Be open to suggestions

### 6. Merge Requirements

**Before merging:**
- ✅ All tests pass
- ✅ At least 1 approval from maintainer
- ✅ All review comments addressed
- ✅ No merge conflicts
- ✅ CI/CD checks pass (if configured)
- ✅ Documentation updated

---

## Code Review Criteria

### Review Checklist

#### Functionality
- [ ] Code works as intended
- [ ] Edge cases handled
- [ ] Error handling is robust
- [ ] No breaking changes (or documented)

#### Security
- [ ] Input sanitization present
- [ ] CSRF tokens on state changes
- [ ] No hardcoded credentials
- [ ] Secure cookie flags used
- [ ] Timing-safe comparisons (`hash_equals()`)
- [ ] Error messages don't leak info

#### Code Quality
- [ ] Follows PSR-12 standards
- [ ] Clear variable/function names
- [ ] No code duplication
- [ ] Appropriate abstraction level
- [ ] Comments explain why, not what

#### Performance
- [ ] No unnecessary loops
- [ ] Efficient algorithms
- [ ] Proper file I/O handling
- [ ] No memory leaks

#### Testing
- [ ] Tests included (if applicable)
- [ ] Tests pass
- [ ] Coverage is adequate
- [ ] Manual testing documented

#### Documentation
- [ ] PHPDoc comments present
- [ ] README updated (if needed)
- [ ] CHANGELOG updated
- [ ] Complex logic explained

---

## Documentation Guidelines

### PHPDoc Standards

```php
/**
 * One-line summary (under 80 chars)
 * 
 * Optional longer description explaining:
 * - What the function does
 * - Why it exists
 * - How it should be used
 * 
 * @param type $name Description
 * @param type $name2 Description
 * @return type Description
 * @throws ExceptionType When/why it throws
 * 
 * @since version Added in this version
 * @see OtherClass::method() Related code
 * @link https://example.com/docs External docs
 * 
 * @example
 * $result = exampleFunction('test', 42);
 * echo $result; // Outputs: processed test 42
 */
```

### Markdown Documentation

- Use headers for structure (##, ###)
- Include code examples with syntax highlighting
- Use tables for comparisons
- Add diagrams for complex flows (ASCII art is fine)
- Link to related documentation

### Code Comments

```php
// ✅ Good: Explains why
// Use timing-safe comparison to prevent timing attacks
if (hash_equals($expected, $actual)) {}

// ❌ Bad: Explains what (obvious from code)
// Compare expected and actual
if ($expected === $actual) {}

// ✅ Good: Documents security consideration
// CRITICAL: Validate CSRF token BEFORE processing ANY action
// to prevent Cross-Site Request Forgery attacks
if (!validateCsrfToken($token, $secret)) {
    http_response_code(403);
    die('CSRF validation failed');
}
```

---

## Release Process

### Version Numbering

We follow **Semantic Versioning** (SemVer):

```
MAJOR.MINOR.PATCH

Example: 4.2.0
         │ │ │
         │ │ └─ Patch: Bug fixes (backwards compatible)
         │ └─── Minor: New features (backwards compatible)
         └───── Major: Breaking changes
```

### When to Bump Versions

- **MAJOR** (X.0.0): Breaking changes, API changes
- **MINOR** (X.Y.0): New features, security enhancements
- **PATCH** (X.Y.Z): Bug fixes, documentation updates

### Release Checklist

- [ ] All tests pass
- [ ] CHANGELOG.md updated
- [ ] README.md version badge updated
- [ ] Documentation reviewed
- [ ] Security audit if needed
- [ ] Tag release in Git
- [ ] Create GitHub release with notes

---

## Getting Help

### Resources

- **Documentation:** See `README.md` and `Documentation/` folder
- **Security:** See `SECURITY.md` for vulnerability reporting
- **CSRF Guide:** See `CSRF-PROTECTION.md` for CSRF implementation
- **HMAC Guide:** See `Documentation/HMAC-AUTHENTICATION.md`

### Communication Channels

- **GitHub Issues:** For bug reports and feature requests
- **GitHub Discussions:** For questions and general discussion
- **Security Email:** security@example.com (for security issues only)

### Questions?

Before asking:
1. Check existing documentation
2. Search closed issues/PRs
3. Review code comments

If still unclear:
1. Open a GitHub Discussion
2. Or comment on a related issue
3. Or ask in your PR (for PR-specific questions)

---

## Recognition

### Contributors

Contributors are listed in:
- GitHub Contributors page
- CHANGELOG.md (for significant contributions)
- Security Hall of Fame (for security researchers)

### Types of Recognition

- 🏆 **Security Hall of Fame** - Valid security vulnerability reports
- ⭐ **Feature Contributors** - Significant new features
- 🐛 **Bug Hunters** - Multiple bug fixes
- 📝 **Documentation Heroes** - Major documentation improvements

---

## License

By contributing, you agree that your contributions will be licensed under the **MIT License**.

---

**Thank you for contributing to making contact forms more secure!** 🔒

---

**Last Updated:** October 2025  
**Contributing Guide Version:** 1.0.0

---

*Have questions about contributing? Open a [GitHub Discussion](https://github.com/yourusername/contact-form-abuse-prevention/discussions) and we'll help you get started!*
