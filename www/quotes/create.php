<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Vérifier l'authentification
requireAuth();

$pdo = getDBConnection();
$userId = getUserId();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        // Récupérer les données
        $clientName = sanitize($_POST['client_name'] ?? '');
        $clientEmail = sanitize($_POST['client_email'] ?? '');
        $clientPhone = sanitize($_POST['client_phone'] ?? '');
        $clientAddress = sanitize($_POST['client_address'] ?? '');
        $quoteDate = $_POST['quote_date'] ?? date('Y-m-d');
        $validityDays = (int) ($_POST['validity_days'] ?? 30);
        $paymentTerms = sanitize($_POST['payment_terms'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        // Items
        $itemDescriptions = $_POST['item_description'] ?? [];
        $itemQuantities = $_POST['item_quantity'] ?? [];
        $itemUnitPrices = $_POST['item_unit_price'] ?? [];

        // Validation
        if (empty($clientName)) {
            $errors[] = "Le nom du client est requis.";
        }

        if (!empty($clientEmail) && !isValidEmail($clientEmail)) {
            $errors[] = "L'adresse email du client est invalide.";
        }

        if (empty($itemDescriptions) || count($itemDescriptions) === 0) {
            $errors[] = "Ajoutez au moins une ligne au devis.";
        }

        // Calculer le total
        $totalAmount = 0;
        $items = [];

        foreach ($itemDescriptions as $index => $description) {
            if (empty($description)) continue;

            $quantity = (float) ($itemQuantities[$index] ?? 0);
            $unitPrice = (float) ($itemUnitPrices[$index] ?? 0);
            $lineTotal = $quantity * $unitPrice;

            $items[] = [
                'description' => sanitize($description),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
                'position' => $index
            ];

            $totalAmount += $lineTotal;
        }

        if (empty($items)) {
            $errors[] = "Ajoutez au moins une ligne valide au devis.";
        }

        // Créer le devis
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Générer le numéro de devis
                $quoteNumber = generateQuoteNumber($pdo);

                // Insérer le devis
                $stmt = $pdo->prepare("
                    INSERT INTO quotes (
                        user_id, quote_number, client_name, client_email, client_phone, client_address,
                        quote_date, total_amount, status, validity_days, payment_terms, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $userId, $quoteNumber, $clientName, $clientEmail, $clientPhone, $clientAddress,
                    $quoteDate, $totalAmount, $status, $validityDays, $paymentTerms, $notes
                ]);

                $quoteId = $pdo->lastInsertId();

                // Insérer les lignes
                $stmt = $pdo->prepare("
                    INSERT INTO quote_items (quote_id, description, quantity, unit_price, total, position)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($items as $item) {
                    $stmt->execute([
                        $quoteId,
                        $item['description'],
                        $item['quantity'],
                        $item['unit_price'],
                        $item['total'],
                        $item['position']
                    ]);
                }

                $pdo->commit();

                setFlashMessage('success', "Devis {$quoteNumber} créé avec succès !");
                redirect("/quotes/view.php?id={$quoteId}");

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Une erreur est survenue lors de la création du devis.";
                error_log("Erreur création devis: " . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Nouveau devis';
include '../includes/header.php';
?>

<div class="card-header">
    <h1>Créer un nouveau devis</h1>
</div>

<div class="card">
    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="quoteForm">
        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

        <h3>Informations client</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="client_name">Nom du client *</label>
                <input type="text" id="client_name" name="client_name" required value="<?php echo e($_POST['client_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="client_email">Email</label>
                <input type="email" id="client_email" name="client_email" value="<?php echo e($_POST['client_email'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="client_phone">Téléphone</label>
                <input type="tel" id="client_phone" name="client_phone" value="<?php echo e($_POST['client_phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="client_address">Adresse</label>
                <input type="text" id="client_address" name="client_address" value="<?php echo e($_POST['client_address'] ?? ''); ?>">
            </div>
        </div>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <h3>Informations du devis</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="quote_date">Date du devis</label>
                <input type="date" id="quote_date" name="quote_date" value="<?php echo $_POST['quote_date'] ?? date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="validity_days">Validité (jours)</label>
                <input type="number" id="validity_days" name="validity_days" value="<?php echo $_POST['validity_days'] ?? 30; ?>" min="1">
            </div>

            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="draft">Brouillon</option>
                    <option value="sent">Envoyé</option>
                    <option value="accepted">Accepté</option>
                    <option value="rejected">Refusé</option>
                </select>
            </div>
        </div>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <h3>Lignes du devis</h3>
        <div id="quoteItems">
            <div class="quote-item">
                <div class="form-group">
                    <label>Description *</label>
                    <input type="text" name="item_description[]" required placeholder="Description de l'article ou service">
                </div>
                <div class="form-group">
                    <label>Quantité *</label>
                    <input type="number" name="item_quantity[]" required step="0.01" min="0.01" value="1" class="item-quantity">
                </div>
                <div class="form-group">
                    <label>Prix unitaire *</label>
                    <input type="number" name="item_unit_price[]" required step="0.01" min="0" value="0" class="item-price">
                </div>
                <div class="form-group">
                    <label>Total</label>
                    <input type="text" class="item-total" readonly value="0,00 €">
                </div>
                <div>
                    <label>&nbsp;</label>
                    <button type="button" class="btn-remove" onclick="removeItem(this)" style="display: none;">✕</button>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-secondary btn-add-item" onclick="addItem()">+ Ajouter une ligne</button>

        <div class="quote-total">
            <h3>Total HT: <span id="grandTotal">0,00 €</span></h3>
        </div>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <h3>Conditions et notes</h3>
        <div class="form-group">
            <label for="payment_terms">Conditions de paiement</label>
            <textarea id="payment_terms" name="payment_terms" rows="3"><?php echo e($_POST['payment_terms'] ?? 'Paiement à 30 jours'); ?></textarea>
        </div>

        <div class="form-group">
            <label for="notes">Notes additionnelles</label>
            <textarea id="notes" name="notes" rows="3"><?php echo e($_POST['notes'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Créer le devis</button>
            <a href="/quotes/" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script src="/assets/js/quote-form.js"></script>

<?php include '../includes/footer.php'; ?>
