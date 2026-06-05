<?php
/**
 * Page de scan de code-barres
 * Étudiant 2 : Front-End & Hardware
 */
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include_once("../../includes/fonctions-produits.php");

// Vérifier la permission
exiger_permission("creer_facture");
include("../../includes/header.php");

$produits = chargerProduits();
$codesProduits = array_values(array_map(function($produit) {
    return (string)($produit["code_barre"] ?? "");
}, $produits));
?>

<h2>Scanner un Code-barres</h2>

<div id="scanner-result" class="scanner-result">
    <p>Cliquez sur le bouton ci-dessous pour ouvrir le scanner.</p>
    <p>Le code-barre détecté sera automatiquement copié dans le champ de la facture.</p>
</div>

<button type="button" class="btn btn-primary" onclick="openScannerCallback()">
    Ouvrir le scanner
</button>

<a href="nouvelle-facture.php" class="btn">Retour à la facturation</a>

<script src="../../assets/js/scanner.js"></script>
<script>
const codesProduits = <?php echo json_encode($codesProduits, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

function openScannerCallback() {
    window.Scanner.openScannerModal(function(codeBarre) {
        const resultDiv = document.getElementById('scanner-result');
        renderScannerResult(resultDiv, codeBarre, true);
        
        // Stocker le code-barre pour la page de facturation
        localStorage.setItem('codeBarreScanne', codeBarre);
        
        // Rediriger vers la page de facturation après 2 secondes
        setTimeout(function() {
            window.location.href = 'nouvelle-facture.php';
        }, 2000);
    }, {
        knownCodes: codesProduits,
        requiredReads: 3
    });
}

// Vérifier si un code-barre a été scanné précédemment
document.addEventListener('DOMContentLoaded', function() {
    const savedCode = localStorage.getItem('codeBarreScanne');
    if (savedCode) {
        renderScannerResult(document.getElementById('scanner-result'), savedCode, false);
    }
});

function renderScannerResult(container, code, redirecting) {
    container.replaceChildren();

    const title = document.createElement('p');
    title.className = redirecting ? 'result-success' : 'result-info';
    title.textContent = redirecting ? 'Code-barre détecté.' : 'Code-barre précédent';

    const codeLine = document.createElement('p');
    const label = document.createElement('strong');
    label.textContent = 'Code : ';
    codeLine.append(label, document.createTextNode(code));

    container.append(title, codeLine);

    if (redirecting) {
        const message = document.createElement('p');
        message.textContent = 'Redirection vers la page de facturation...';
        container.appendChild(message);
        return;
    }

    const actions = document.createElement('div');
    actions.className = 'inline-actions';

    const useButton = document.createElement('button');
    useButton.type = 'button';
    useButton.className = 'btn';
    useButton.textContent = 'Utiliser ce code';
    useButton.addEventListener('click', utiliserCeCode);

    const clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.className = 'btn btn-danger';
    clearButton.textContent = 'Effacer';
    clearButton.addEventListener('click', effacerCode);

    actions.append(useButton, clearButton);
    container.appendChild(actions);
}

function utiliserCeCode() {
    const code = localStorage.getItem('codeBarreScanne');
    if (code) {
        window.location.href = 'nouvelle-facture.php?code=' + encodeURIComponent(code);
    }
}

function effacerCode() {
    localStorage.removeItem('codeBarreScanne');
    location.reload();
}
</script>

<?php include("../../includes/footer.php"); ?>
