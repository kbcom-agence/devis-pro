<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    redirect('/dashboard/');
}

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$success = false;
$validToken = false;

// Vérifier le token
if (!empty($token)) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT id, reset_token_expiry
        FROM users
        WHERE reset_token = ? AND reset_token_expiry > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $validToken = true;
    } else {
        $errors[] = "Ce lien de réinitialisation est invalide ou a expiré.";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($password) || strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Mettre à jour le mot de passe
        if (empty($errors)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE users
                SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL
                WHERE id = ?
            ");

            if ($stmt->execute([$passwordHash, $user['id']])) {
                $success = true;
                setFlashMessage('success', 'Votre mot de passe a été réinitialisé avec succès !');
            } else {
                $errors[] = "Une erreur est survenue lors de la réinitialisation.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - Gestion de Devis</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Nouveau mot de passe</h1>

            <?php if ($success): ?>
                <div class="flash-message flash-success">
                    Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.
                </div>
                <p class="auth-links">
                    <a href="/auth/login.php" class="btn btn-primary btn-block">Se connecter</a>
                </p>
            <?php elseif ($validToken): ?>
                <?php if (!empty($errors)): ?>
                    <div class="flash-message flash-error">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                    <input type="hidden" name="token" value="<?php echo e($token); ?>">

                    <div class="form-group">
                        <label for="password">Nouveau mot de passe (min. 8 caractères)</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Réinitialiser le mot de passe</button>
                </form>
            <?php else: ?>
                <div class="flash-message flash-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
                <p class="auth-links">
                    <a href="/auth/forgot-password.php">Demander un nouveau lien</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
