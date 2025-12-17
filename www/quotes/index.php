<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Vérifier l'authentification
requireAuth();

$pdo = getDBConnection();
$userId = getUserId();

// Filtres
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Requête de base
$sql = "SELECT id, quote_number, client_name, client_email, total_amount, status, quote_date, created_at
        FROM quotes
        WHERE user_id = ?";
$params = [$userId];

// Ajouter les filtres
if (!empty($search)) {
    $sql .= " AND (client_name LIKE ? OR quote_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$quotes = $stmt->fetchAll();

$pageTitle = 'Mes devis';
include '../includes/header.php';
?>

<div class="card-header">
    <h1>Mes devis</h1>
</div>

<div class="card">
    <form method="GET" style="margin-bottom: 1.5rem;">
        <div class="form-row">
            <div class="form-group">
                <label for="search">Rechercher</label>
                <input type="text" id="search" name="search" placeholder="Client ou numéro de devis..." value="<?php echo e($search); ?>">
            </div>

            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                    <option value="sent" <?php echo $statusFilter === 'sent' ? 'selected' : ''; ?>>Envoyé</option>
                    <option value="accepted" <?php echo $statusFilter === 'accepted' ? 'selected' : ''; ?>>Accepté</option>
                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Refusé</option>
                </select>
            </div>

            <div class="form-group" style="display: flex; align-items: end; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="/quotes/" class="btn btn-secondary">Réinitialiser</a>
            </div>
        </div>
    </form>

    <?php if (empty($quotes)): ?>
        <p style="text-align: center; color: var(--text-light); padding: 2rem;">
            <?php if (!empty($search) || !empty($statusFilter)): ?>
                Aucun devis ne correspond à vos critères de recherche.
            <?php else: ?>
                Aucun devis pour le moment.
                <br><br>
                <a href="/quotes/create.php" class="btn btn-primary">Créer votre premier devis</a>
            <?php endif; ?>
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                    <tr>
                        <td><?php echo e($quote['quote_number']); ?></td>
                        <td><?php echo e($quote['client_name']); ?></td>
                        <td><?php echo e($quote['client_email'] ?: '-'); ?></td>
                        <td><?php echo formatDate($quote['quote_date']); ?></td>
                        <td><strong><?php echo formatMoney($quote['total_amount']); ?></strong></td>
                        <td>
                            <span class="status-badge <?php echo getStatusClass($quote['status']); ?>">
                                <?php echo translateStatus($quote['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/quotes/view.php?id=<?php echo $quote['id']; ?>" class="btn btn-sm btn-secondary">Voir</a>
                                <a href="/quotes/edit.php?id=<?php echo $quote['id']; ?>" class="btn btn-sm btn-primary">Éditer</a>
                                <a href="/quotes/delete.php?id=<?php echo $quote['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce devis ?');">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 1.5rem;">
            <p style="color: var(--text-light);">
                <?php echo count($quotes); ?> devis trouvé<?php echo count($quotes) > 1 ? 's' : ''; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 1.5rem;">
    <a href="/quotes/create.php" class="btn btn-primary">+ Nouveau devis</a>
</div>

<?php include '../includes/footer.php'; ?>
