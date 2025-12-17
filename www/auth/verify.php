<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$token = $_GET['token'] ?? '';
$success = false;
$error = '';

if (empty($token)) {
    $error = "Token de vérification manquant.";
} else {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Token de vérification invalide.";
    } elseif ($user['is_verified']) {
        $error = "Ce compte est déjà vérifié.";
    } else {
        // Vérifier le compte
        $stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE, verification_token = NULL WHERE id = ?");
        if ($stmt->execute([$user['id']])) {
            $success = true;
            setFlashMessage('success', 'Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.');
        } else {
            $error = "Une erreur est survenue lors de la vérification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du compte - Gestion de Devis</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Vérification du compte</h1>

            <?php if ($success): ?>
                <div class="flash-message flash-success">
                    Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.
                </div>
                <p class="auth-links">
                    <a href="/auth/login.php" class="btn btn-primary btn-block">Se connecter</a>
                </p>
            <?php else: ?>
                <div class="flash-message flash-error">
                    <?php echo e($error); ?>
                </div>
                <p class="auth-links">
                    <a href="/auth/register.php">Créer un compte</a> |
                    <a href="/auth/login.php">Se connecter</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
