<?php
/**
 * Escape untrusted values before placing them in HTML text or quoted attributes.
 * Use this when displaying values, not when storing them in MySQL.
 */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * The registration password must have at least 8 characters,
 * one lowercase letter, one uppercase letter, and one number.
 */
function password_meets_policy(string $password): bool
{
    $length = function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password);

    return $length >= 8
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1;
}

function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

function require_login()
{
    if (empty($_SESSION['user_id'])) {
        $_SESSION['flash_message'] = 'Please log in to open that page.';
        $_SESSION['flash_type'] = 'error';
        redirect('login.php');
    }
}

function current_user_id()
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function set_flash($message, $type = 'success')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash()
{
    if (empty($_SESSION['flash_message'])) {
        return null;
    }

    $flash = [
        'message' => $_SESSION['flash_message'],
        'type' => $_SESSION['flash_type'] ?? 'success'
    ];

    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return $flash;
}

function log_activity($pdo, $entityName, $recordId, $actionName, $description, $userId = null)
{
    if ($userId === null) {
        $userId = current_user_id();
    }

    $sql = 'INSERT INTO activity_logs
            (entity_name, record_id, action_name, user_id, description)
            VALUES (?, ?, ?, ?, ?)';
    $statement = $pdo->prepare($sql);
    $statement->execute([$entityName, $recordId, $actionName, $userId, $description]);
}
?>
