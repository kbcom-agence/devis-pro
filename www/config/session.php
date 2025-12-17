<?php
/**
 * Configuration des sessions sécurisées
 */

// Configuration des paramètres de session avant de démarrer
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Générer un token CSRF si il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

/**
 * Vérifie si l'utilisateur est connecté, sinon redirige vers login
 */
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /auth/login.php');
        exit;
    }
}

/**
 * Obtient l'ID de l'utilisateur connecté
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtient l'email de l'utilisateur connecté
 */
function getUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

/**
 * Vérifie le token CSRF
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtient le token CSRF
 */
function getCsrfToken() {
    return $_SESSION['csrf_token'] ?? '';
}
