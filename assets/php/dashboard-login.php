<?php
session_start();

/////////////////////////////
// 0) Security headers
/////////////////////////////
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

/////////////////////////////
// 1) Session bootstrap (hardened, host-specific)
/////////////////////////////
$IS_HTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$COOKIE_DOMAIN = $_SERVER['HTTP_HOST'] ?? '';
$COOKIE_PATH   = '/';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'domain'   => $COOKIE_DOMAIN,
        'path'     => $COOKIE_PATH,
        'secure'   => $IS_HTTPS,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

/////////////////////////////
// 2) Paths & .env loader
/////////////////////////////
define('PHP_DIR', __DIR__);
define('LOG_DIR', PHP_DIR . '/logs');
define('ENV_FILE', PHP_DIR . '/.env.prod');
define('LOGIN_PATH', '/assets/php/dashboard-login.php');

if (!is_dir(LOG_DIR)) { @mkdir(LOG_DIR, 0755, true); }

function loadEnvFile(string $file): array {
    if (!is_file($file)) return [];
    $env = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, '=') !== false && $line[0] !== '#') {
            [$k, $v] = explode('=', trim($line), 2);
            $env[trim($k)] = trim($v, '"\'');
        }
    }
    return $env;
}
$env = loadEnvFile(ENV_FILE);

$PASSWORD_HASH = getenv('DASHBOARD_PASSWORD_HASH') ?: ($env['DASHBOARD_PASSWORD_HASH'] ?? '');
$REDIRECT_TO   = getenv('DASHBOARD_REDIRECT')     ?: ($env['DASHBOARD_REDIRECT'] ?? '/assets/php/dashboard.php');
$AUTH_DEBUG    = (getenv('AUTH_DEBUG') ?: ($env['AUTH_DEBUG'] ?? '')) === '1';
$DASHBOARD_SECRET = getenv('DASHBOARD_SECRET') ?: ($env['DASHBOARD_SECRET'] ?? '');

if (!$PASSWORD_HASH) {
    http_response_code(500);
    echo 'Server configuration error (DASHBOARD_PASSWORD_HASH not set).';
    exit;
}
if (!$DASHBOARD_SECRET) {
    http_response_code(500);
    echo 'Server configuration error (DASHBOARD_SECRET not set).';
    exit;
}

/////////////////////////////
// 3) Rate limit config & helpers
/////////////////////////////
const RL_WINDOW_SEC = 15 * 60;
const RL_MAX_FAILS  = 5;
define('RL_FILE', LOG_DIR . '/login_attempts.jsonl');

function client_ip(): string   { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function user_agent(): string  { return substr($_SERVER['HTTP_USER_AGENT'] ?? '-', 0, 300); }
function rl_now(): int         { return time(); }

function rl_read_all(): array {
    if (!is_file(RL_FILE)) return [];
    $rows = [];
    $fp = fopen(RL_FILE, 'r'); if (!$fp) return $rows;
    if (flock($fp, LOCK_SH)) {
        while (($line = fgets($fp)) !== false) {
            $row = json_decode($line, true);
            if (is_array($row)) $rows[] = $row;
        }
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $rows;
}
function rl_write_all(array $rows): void {
    $fp = fopen(RL_FILE, 'w'); if (!$fp) return;
    if (flock($fp, LOCK_EX)) {
        foreach ($rows as $row) fwrite($fp, json_encode($row) . "\n");
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}
function rl_log_fail(string $ip): void {
    $entry = ['ts'=>rl_now(),'ip'=>$ip,'ua'=>user_agent(),'type'=>'failed_login'];
    $fp = fopen(RL_FILE, 'a');
    if ($fp && flock($fp, LOCK_EX)) {
        fwrite($fp, json_encode($entry) . "\n");
        flock($fp, LOCK_UN); fclose($fp);
    } elseif ($fp) fclose($fp);
}
function rl_reset_ip(string $ip): void {
    $rows = rl_read_all(); $now = rl_now(); $winStart = $now - RL_WINDOW_SEC;
    $changed = false; $keep = [];
    foreach ($rows as $r) {
        if ($r['ip'] === $ip && $r['type'] === 'failed_login' && $r['ts'] >= $winStart) {
            $changed = true; continue;
        }
        $keep[] = $r;
    }
    if ($changed) rl_write_all($keep);
}
function rl_is_limited(string $ip): bool {
    $rows = rl_read_all(); $now = rl_now(); $winStart = $now - RL_WINDOW_SEC;
    $fails = 0;
    foreach ($rows as $r) {
        if ($r['ip'] === $ip && $r['type'] === 'failed_login' && $r['ts'] >= $winStart) {
            $fails++;
        }
    }
    return $fails >= RL_MAX_FAILS;
}

/////////////////////////////
// 4) CSRF
/////////////////////////////
function ensure_csrf(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function validate_csrf(string $tokenFromPost): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $tokenFromPost);
}

/////////////////////////////
// 5) Redirect helper (validated + loop-safe)
/////////////////////////////
function safe_redirect(?string $path): void {
    $path = trim((string)$path);
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($path === '' || preg_match('/^\s*https?:\/\//i', $path) || $path[0] !== '/' || preg_match('/[\r\n]/', $path)) return;
    if ($path === $currentPath || $path === LOGIN_PATH) return;
    header('Location: '.$path, true, 302);
    exit;
}

/////////////////////////////
// 6) Auth helpers & rendering
/////////////////////////////
function is_authenticated(): bool { return !empty($_SESSION['dashboard_auth']); }

function render_login(string $message = '', int $httpStatus = 200): void {
    http_response_code($httpStatus);
    $csrf = ensure_csrf();
    $msg = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    ?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { background: #0d1117; color: #e9ecef; font-family: 'Segoe UI', Roboto, Arial, sans-serif; }
    .card { max-width: 400px; margin: 80px auto; background: #161b22; border-radius: 12px; padding: 32px; box-shadow: 0 4px 24px rgba(0,0,0,0.3); }
    h2 { color: #fff; margin-bottom: 24px; }
    label { display: block; margin-bottom: 8px; color: #8b949e; }
    input[type="password"], input[type="text"] { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #30363d; background: #0d1117; color: #fff; margin-bottom: 16px; }
    button { width: 100%; padding: 12px; border-radius: 6px; border: none; background: #3498db; color: #fff; font-size: 1em; cursor: pointer; }
    .msg { color: #e74c3c; margin-bottom: 16px; }
  </style>
</head>
<body>
  <div class="card">
    <h2>Dashboard Login</h2>
    <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" required autofocus>
      <input type="hidden" name="csrf" value="<?= $csrf ?>">
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
<?php
    exit;
}

/////////////////////////////
// 7) GET: wenn auth → redirect (loop-safe), sonst Formular
/////////////////////////////
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_COOKIE['dashboard_token'] ?? '';
    if ($token && $DASHBOARD_SECRET) {
        // Prüfe Token wie dashboard.php
        [$payload, $signature] = explode('.', $token, 2) + [null, null];
        $expected = hash_hmac('sha256', $payload, $DASHBOARD_SECRET);
        $data = json_decode(base64_decode($payload), true);
        if ($signature && hash_equals($expected, $signature) && $data && isset($data['exp']) && $data['exp'] >= time()) {
            safe_redirect($REDIRECT_TO);
        }
    }
    render_login('');
}

/////////////////////////////
// 8) POST: verify & redirect
/////////////////////////////
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); header('Allow: GET, POST'); echo 'Method Not Allowed'; exit;
}

// CSRF
$csrfPost = (string)($_POST['csrf'] ?? '');
if (!validate_csrf($csrfPost)) {
    render_login('Ungültiges oder fehlendes CSRF-Token.', 400);
}

// Rate-limit
$ip = client_ip();
if (rl_is_limited($ip)) {
    render_login('Zu viele Fehlversuche. Bitte in 15 Minuten erneut versuchen.', 429);
}

// Passwort prüfen
$inputPassword = (string)($_POST['password'] ?? '');
if (!password_verify($inputPassword, $PASSWORD_HASH)) {
    rl_log_fail($ip);
    render_login('Ungültige Zugangsdaten.', 401);
}

// Erfolg: Rate-Limit zurücksetzen
rl_reset_ip($ip);
session_regenerate_id(true);
$_SESSION['dashboard_auth'] = true;
$_SESSION['auth_since'] = time();
$_SESSION['last_seen']  = time();

// CSRF-Token für JWT und Cookie
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

// JWT-Payload
$payloadArr = [
    'exp' => time() + 86400, // 24h gültig
    'csrf' => $csrfToken
];
$jwtPayload = base64_encode(json_encode($payloadArr));
$jwtSignature = hash_hmac('sha256', $jwtPayload, $DASHBOARD_SECRET);
$jwtToken = $jwtPayload . '.' . $jwtSignature;

// Setze Token-Cookie (HttpOnly)
setcookie('dashboard_token', $jwtToken, [
    'expires' => time() + 86400,
    'path' => '/',
    'secure' => $IS_HTTPS,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Setze CSRF-Cookie (nicht HttpOnly, da JS Zugriff braucht)
setcookie('csrf_token', $csrfToken, [
    'expires' => time() + 86400,
    'path' => '/',
    'secure' => $IS_HTTPS,
    'httponly' => false,
    'samesite' => 'Strict'
]);

// Redirect (validiert + loop-safe)
safe_redirect($REDIRECT_TO);

// Fallback OK-Seite
http_response_code(200);
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Login erfolgreich</title>
</head>
<body>
  <h2>Login erfolgreich. <a href="<?= htmlspecialchars($REDIRECT_TO) ?>">Weiter zum Dashboard</a></h2>
</body>
</html>