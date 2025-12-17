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
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($email) || !isValidEmail($email)) {
            $errors[] = "Veuillez saisir une adresse email valide.";
        }

        if (empty($password) || strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Vérifier si l'email existe déjà
        if (empty($errors)) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = "Cette adresse email est déjà utilisée.";
            }
        }

        // Créer le compte
        if (empty($errors)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $verificationToken = bin2hex(random_bytes(32));

            $stmt = $pdo->prepare("
                INSERT INTO users (email, password_hash, verification_token)
                VALUES (?, ?, ?)
            ");

            if ($stmt->execute([$email, $passwordHash, $verificationToken])) {
                // Envoyer l'email de vérification
                sendVerificationEmail($email, $verificationToken);

                $success = true;
                setFlashMessage('success', 'Compte créé avec succès ! Veuillez vérifier votre email pour activer votre compte.');
            } else {
                $errors[] = "Une erreur est survenue lors de la création du compte.";
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
    <title>Inscription - Gestion de Devis</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Inscription</h1>

            <?php if ($success): ?>
                <div class="flash-message flash-success">
                    Compte créé avec succès ! Consultez vos emails (ou les logs Docker) pour activer votre compte.
                </div>
                <p class="auth-links">
                    <a href="/auth/login.php">Se connecter</a>
                </p>
            <?php else: ?>
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
                        <label for="password">Mot de passe (min. 8 caractères)</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                </form>

                <p class="auth-links">
                    Déjà inscrit ? <a href="/auth/login.php">Se connecter</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
