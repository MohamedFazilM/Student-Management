<?php
session_start();

/**
 * Require the user to be logged in.
 * If not, redirect to login page.
 */
function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require the logged-in user to be an admin.
 * If not, redirect to index with an error flash.
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

/**
 * Returns true if the current user is an admin.
 */
function isAdmin(): bool {
    return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Returns true if someone is logged in.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Return the display name for the current user.
 */
function currentUser(): string {
    return htmlspecialchars($_SESSION['username'] ?? 'Guest');
}
