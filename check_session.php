<?php
/**
 * check_session.php
 *
 * Starts the session and makes $_SESSION['user'] available.
 * Pages are PUBLIC by default — no forced redirect.
 *
 * To protect a specific page or action, call requireVisitorLogin().
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirects to login if not logged in.
 * Saves the intended URL so login.html can bounce the user back after login.
 */
function requireVisitorLogin(): void
{
    $u = $_SESSION['user'] ?? null;
    if ($u && isset($u['role'])) {
        return; // already logged in — nothing to do
    }
    $target = $_SERVER['REQUEST_URI'] ?? 'mainPage.php';
    $_SESSION['login_redirect'] = $target;
    header('Location: login.html?reason=login_required&redirect=' . urlencode($target));
    exit;
}

function requireWorkerLogin(): void
{
    $u = $_SESSION['user'] ?? null;
    if ($u && isset($u['role']) && ($u['role'] === 'worker' || $u['role'] === 'admin')) {
        return; // valid staff — allow through
    }
    $target = $_SERVER['REQUEST_URI'] ?? 'mainpageworker.php';
    $_SESSION['login_redirect'] = $target;
    header('Location: staff-login.php?reason=login_required');
    exit;
}

/** Returns true if any user (visitor / admin / worker) is logged in. */
function isLoggedIn(): bool
{
    return isset($_SESSION['user']['role']);
}

/** Returns true only for a logged-in visitor. */
function isVisitor(): bool
{
    return ($_SESSION['user']['role'] ?? '') === 'visitor';
}
