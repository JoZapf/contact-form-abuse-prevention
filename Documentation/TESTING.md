# Testing

Reproducible test procedures for verifying the security hardening measures.

---

## Syntax Check

Validates all PHP files for syntax errors without executing them.

```bash
# On the server
find /path/to/assets/php -maxdepth 1 -name "*.php" -exec php -l {} \;
```

```powershell
# On Windows (PowerShell)
Get-ChildItem "assets\php\*.php" -File | ForEach-Object {
    $result = php -l $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) { Write-Host "FAIL $($_.Name): $result" }
    else { Write-Host "OK   $($_.Name)" }
}
```

---

## Functional Tests (Production)

The test suite `security-tests.sh` runs 31 curl-based checks against the live server to verify all hardening phases.

```bash
# Upload and run via SSH
scp assets/php/security-tests.sh user@server:~/
ssh user@server 'bash ~/security-tests.sh'
```

### Test Matrix

| Test | Scope | What is verified | Expected |
|------|-------|------------------|----------|
| T1 | Access control | 19 blocked paths return 403 | All 403 Forbidden |
| T2 | Endpoint availability | Form handler + login reachable | 200, 422, or 429 (not 403) |
| T3 | CORS policy | OPTIONS response header | Configured domain (not `*`) |
| T4 | CSRF / Init | Init endpoint `?init=1` | JSON with csrf_token + captcha |
| T5 | CSRF enforcement | POST without CSRF token | Rejection (or WARN in migration mode) |
| T6 | Cross-origin rejection | POST with foreign Origin header | Foreign origin not reflected |
| T7 | Directory listing | Directory access | No "Index of" response |
| T8 | Document blocking | Markdown files | 403 Forbidden |
| T9 | Session security | Set-Cookie flags | HttpOnly, Secure, SameSite=Strict |

### Expected Warnings

- **T2:** May return 429 (Too Many Requests) when tests run from the server's own IP — this confirms rate limiting is active, not a failure.
- **T5:** In migration mode, a POST without token is accepted because the session contains no token yet. After frontend deployment, strict mode should be activated.
- **T9:** Cookie flags are only visible when the server sends a `Set-Cookie` header. On repeated calls the header may be absent — WARN is expected.

---

## Recommended Future Testing

**Static analysis (PHPStan):** Catches type errors, undefined variables, and dead code paths without execution.
```bash
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse assets/php/ --level=5
```

**Unit tests (PHPUnit):** Tests individual classes in isolation (BlocklistManager, LoginRateLimiter, AbuseLogger).
```bash
composer require --dev phpunit/phpunit
vendor/bin/phpunit tests/
```

**Code coverage:** Requires PHPUnit + Xdebug.
```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage/
```

---

## Test History

| Date | Type | Result | Notes |
|------|------|--------|-------|
| 2026-03-25 | Syntax check | 18/18 OK | PHP 8.4.0 |
| 2026-03-25 | Functional tests | 30 PASS, 1 WARN | Rate limiting confirmed (429). CSRF migration mode active (by design). |
