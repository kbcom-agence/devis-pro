<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    redirect('/dashboard/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($email) || empty($password)) {
            $errors[] = "Veuillez remplir tous les champs.";
        }

        // Vérifier les identifiants
        if (empty($errors)) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, email, password_hash, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Vérifier si le compte est vérifié
                if (!$user['is_verified']) {
                    $errors[] = "Veuillez d'abord vérifier votre compte via l'email reçu.";
                } else {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];

                    // Rediriger vers la page demandée ou le dashboard
                    $redirectTo = $_SESSION['redirect_after_login'] ?? '/dashboard/';
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirectTo);
                }
            } else {
                $errors[] = "Email ou mot de passe incorrect.";
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
    <title>Connexion - Gestion de Devis</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Connexion</h1>

            <?php displayFlashMessage(); ?>

            <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>

            <p class="auth-links">
                <a href="/auth/forgot-password.php">Mot de passe oublié ?</a>
            </p>

            <p class="auth-links">
                Pas encore inscrit ? <a href="/auth/register.php">Créer un compte</a>
            </p>
        </div>
    </div>
</body>
</html>
