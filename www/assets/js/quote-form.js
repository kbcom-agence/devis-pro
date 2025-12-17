// Gestion dynamique du formulaire de devis

// Fonction pour ajouter une nouvelle ligne
function addItem() {
    const container = document.getElementById('quoteItems');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'quote-item';
    itemDiv.innerHTML = `
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
            <button type="button" class="btn-remove" onclick="removeItem(this)">✕</button>
        </div>
    `;

    container.appendChild(itemDiv);

    // Attacher les event listeners pour les nouveaux champs
    attachItemListeners(itemDiv);

    // Afficher les boutons de suppression si plus d'une ligne
    updateRemoveButtons();
}

// Fonction pour supprimer une ligne
function removeItem(button) {
    const item = button.closest('.quote-item');
    item.remove();

    // Recalculer le total
    calculateGrandTotal();

    // Mettre à jour l'affichage des boutons de suppression
    updateRemoveButtons();
}

// Mettre à jour l'affichage des boutons de suppression
function updateRemoveButtons() {
    const items = document.querySelectorAll('.quote-item');
    items.forEach((item, index) => {
        const removeBtn = item.querySelector('.btn-remove');
        if (items.length === 1) {
            removeBtn.style.display = 'none';
        } else {
            removeBtn.style.display = 'block';
        }
    });
}

// Calculer le total d'une ligne
function calculateLineTotal(itemElement) {
    const quantity = parseFloat(itemElement.querySelector('.item-quantity').value) || 0;
    const unitPrice = parseFloat(itemElement.querySelector('.item-price').value) || 0;
    const total = quantity * unitPrice;

    const totalField = itemElement.querySelector('.item-total');
    totalField.value = formatMoney(total);

    return total;
}

// Calculer le total général
function calculateGrandTotal() {
    const items = document.querySelectorAll('.quote-item');
    let grandTotal = 0;

    items.forEach(item => {
        grandTotal += calculateLineTotal(item);
    });

    document.getElementById('grandTotal').textContent = formatMoney(grandTotal);
}

// Formater un montant
function formatMoney(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

// Attacher les event listeners à une ligne
function attachItemListeners(itemElement) {
    const quantityInput = itemElement.querySelector('.item-quantity');
    const priceInput = itemElement.querySelector('.item-price');

    quantityInput.addEventListener('input', calculateGrandTotal);
    priceInput.addEventListener('input', calculateGrandTotal);
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Attacher les listeners aux lignes existantes
    document.querySelectorAll('.quote-item').forEach(attachItemListeners);

    // Calculer le total initial
    calculateGrandTotal();

    // Mettre à jour l'affichage des boutons de suppression
    updateRemoveButtons();
});
