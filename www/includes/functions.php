<?php
/**
 * Fonctions utilitaires
 */

/**
 * Redirige vers une URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Échappe les données HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Nettoie une chaîne de caractères
 */
function sanitize($string) {
    return trim(strip_tags($string));
}

/**
 * Valide une adresse email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Définit un message flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupère et supprime le message flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Affiche le message flash en HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type']; // success, error, warning, info
        $message = e($flash['message']);
        echo "<div class='flash-message flash-{$type}'>{$message}</div>";
    }
}

/**
 * Génère un numéro de devis unique
 */
function generateQuoteNumber($pdo) {
    $year = date('Y');
    $prefix = "DEV-{$year}-";

    // Trouver le dernier numéro de devis de l'année
    $stmt = $pdo->prepare("SELECT quote_number FROM quotes WHERE quote_number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastQuote = $stmt->fetch();

    if ($lastQuote) {
        // Extraire le numéro et incrémenter
        $lastNumber = (int) str_replace($prefix, '', $lastQuote['quote_number']);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

/**
 * Formate un montant en euros
 */
function formatMoney($amount) {
    return number_format($amount, 2, ',', ' ') . ' €';
}

/**
 * Formate une date
 */
function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Traduit le statut du devis
 */
function translateStatus($status) {
    $statuses = [
        'draft' => 'Brouillon',
        'sent' => 'Envoyé',
        'accepted' => 'Accepté',
        'rejected' => 'Refusé'
    ];
    return $statuses[$status] ?? $status;
}

/**
 * Retourne la classe CSS pour un statut
 */
function getStatusClass($status) {
    $classes = [
        'draft' => 'status-draft',
        'sent' => 'status-sent',
        'accepted' => 'status-accepted',
        'rejected' => 'status-rejected'
    ];
    return $classes[$status] ?? '';
}
