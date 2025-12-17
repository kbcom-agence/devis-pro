<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Vérifier l'authentification
requireAuth();

$pdo = getDBConnection();
$userId = getUserId();
$quoteId = (int) ($_GET['id'] ?? 0);

// Vérifier que le devis existe et appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT id, quote_number FROM quotes WHERE id = ? AND user_id = ?");
$stmt->execute([$quoteId, $userId]);
$quote = $stmt->fetch();

if (!$quote) {
    setFlashMessage('error', 'Devis introuvable.');
    redirect('/quotes/');
}

// Supprimer le devis (les lignes seront supprimées automatiquement grâce à ON DELETE CASCADE)
$stmt = $pdo->prepare("DELETE FROM quotes WHERE id = ?");
$stmt->execute([$quoteId]);

setFlashMessage('success', "Le devis {$quote['quote_number']} a été supprimé.");
redirect('/quotes/');
