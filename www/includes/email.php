<?php
/**
 * Système d'envoi d'emails (simulé)
 * Les emails sont affichés dans les logs Docker
 */

/**
 * Envoie un email (simulé - affiche dans les logs)
 */
function sendEmail($to, $subject, $body) {
    $timestamp = date('Y-m-d H:i:s');
    $separator = str_repeat('=', 80);

    $logMessage = "\n{$separator}\n";
    $logMessage .= "[EMAIL] {$timestamp}\n";
    $logMessage .= "To: {$to}\n";
    $logMessage .= "Subject: {$subject}\n";
    $logMessage .= "{$separator}\n";
    $logMessage .= $body . "\n";
    $logMessage .= "{$separator}\n";

    // Écrire dans le log PHP (visible dans docker-compose logs -f web)
    error_log($logMessage);

    return true;
}

/**
 * Envoie un email de vérification
 */
function sendVerificationEmail($email, $token) {
    $verificationLink = "http://localhost:8080/auth/verify.php?token=" . urlencode($token);

    $subject = "Vérification de votre compte";
    $body = "Bonjour,\n\n";
    $body .= "Merci de vous être inscrit sur notre application de gestion de devis.\n\n";
    $body .= "Pour activer votre compte, veuillez cliquer sur le lien suivant :\n";
    $body .= $verificationLink . "\n\n";
    $body .= "Si vous n'avez pas créé de compte, ignorez cet email.\n\n";
    $body .= "Cordialement,\n";
    $body .= "L'équipe de gestion de devis";

    return sendEmail($email, $subject, $body);
}

/**
 * Envoie un email de réinitialisation de mot de passe
 */
function sendPasswordResetEmail($email, $token) {
    $resetLink = "http://localhost:8080/auth/reset-password.php?token=" . urlencode($token);

    $subject = "Réinitialisation de votre mot de passe";
    $body = "Bonjour,\n\n";
    $body .= "Vous avez demandé à réinitialiser votre mot de passe.\n\n";
    $body .= "Pour créer un nouveau mot de passe, veuillez cliquer sur le lien suivant :\n";
    $body .= $resetLink . "\n\n";
    $body .= "Ce lien expirera dans 1 heure.\n\n";
    $body .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
    $body .= "Cordialement,\n";
    $body .= "L'équipe de gestion de devis";

    return sendEmail($email, $subject, $body);
}
