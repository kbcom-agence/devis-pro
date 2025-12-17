<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Gestion de Devis'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="/" class="logo">Devis Pro</a>

            <?php if (isLoggedIn()): ?>
                <ul class="nav-links">
                    <li><a href="/dashboard/">Tableau de bord</a></li>
                    <li><a href="/quotes/">Mes devis</a></li>
                    <li><a href="/quotes/create.php">Nouveau devis</a></li>
                    <li><span style="color: var(--text-light);"><?php echo e(getUserEmail()); ?></span></li>
                    <li><a href="/auth/logout.php" class="btn-logout">Déconnexion</a></li>
                </ul>
            <?php else: ?>
                <ul class="nav-links">
                    <li><a href="/auth/login.php">Connexion</a></li>
                    <li><a href="/auth/register.php">Inscription</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <div class="container">
            <?php displayFlashMessage(); ?>
