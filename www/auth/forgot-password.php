<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';
require_once '../includes/email.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    redirect('/dashboard/');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $email = sanitize($_POST['email'] ?? '');

        // Validation
        if (empty($email) || !isValidEmail($email)) {
            $errors[] = "Veuillez saisir une adresse email valide.";
        }

        if (empty($errors)) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Générer un token de réinitialisation
                $resetToken = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET reset_token = ?, reset_token_expiry = ?
                    WHERE id = ?
                ");
                $stmt->execute([$resetToken, $expiry, $user['id']]);

                // Envoyer l'email
                sendPasswordResetEmail($email, $resetToken);
            }

            // Toujours afficher le message de succès (pour ne pas révéler si l'email existe)
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Gestion de Devis</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Mot de passe oublié</h1>

            <?php if ($success): ?>
                <div class="flash-message flash-success">
                    Si cette adresse email existe dans notre système, vous recevrez un lien de réinitialisation.
                    Consultez vos emails (ou les logs Docker).
                </div>
                <p class="auth-links">
                    <a href="/auth/login.php">Retour à la connexion</a>
                </p>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="flash-message flash-error">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                    Entrez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.
                </p>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Envoyer le lien</button>
                </form>

                <p class="auth-links">
                    <a href="/auth/login.php">Retour à la connexion</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
