<?php
/**
 * CSRF Protection for Game Forms
 * 
 * Lightweight per-session CSRF token for all game POST forms.
 * Reuses the same approach as Admin/csrf.php but independent:
 * - game forms store token in $_SESSION['_csrf_game_token']
 * - Admin panel uses $_SESSION['_csrf_token'] (separate)
 * 
 * Include this file AFTER session_start().
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Generate the game CSRF token once per session.
if (empty($_SESSION['_csrf_game_token'])) {
    $_SESSION['_csrf_game_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('game_csrf_token')) {
    /**
     * Return the current game CSRF token.
     */
    function game_csrf_token(): string
    {
        return $_SESSION['_csrf_game_token'] ?? '';
    }

    /**
     * Emit a hidden <input> for game POST forms.
     * Usage in any .tpl or PHP form: <?php echo game_csrf_field(); ?>
     */
    function game_csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' 
            . htmlspecialchars(game_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify the CSRF token on POST requests.
     * Returns true if valid, false + sets error if invalid.
     * Does NOT die() — caller decides how to handle the failure.
     */
    function game_csrf_verify(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // GET requests don't need CSRF
        }

        $submitted = isset($_POST['_csrf_token']) ? (string)$_POST['_csrf_token'] : '';
        $stored    = game_csrf_token();

        if ($stored === '' || !hash_equals($stored, $submitted)) {
            return false;
        }

        return true;
    }
}
