<?php
/**
 * Page de scan de code-barres
 * Étudiant 2 : Front-End & Hardware
 */
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");
include_once("../../includes/fonctions-produits.php");

// Vérifier la permission
exiger_permission("creer_facture");

$produits = chargerProduits();
$codesProduits = array_values(array_map(function($produit) {
    return (string)($produit["code_barre"] ?? "");
}, $produits));
?>

<h2>Scanner un Code-barres</h2>

<div id="scanner-result" style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 8px;">
    <p>Cliquez sur le bouton ci-dessous pour ouvrir le scanner.</p>
    <p>Le code-barre détecté sera automatiquement copié dans le champ de la facture.</p>
</div>

<button type="button" class="btn btn-primary" onclick="openScannerCallback()">
    📷 Ouvrir le Scanner
</button>

<a href="nouvelle-facture.php" class="btn">Retour à la facturation</a>

<script src="../../assets/js/scanner.js"></script>
<script>
const codesProduits = <?php echo json_encode($codesProduits, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

function openScannerCallback() {
    window.Scanner.openScannerModal(function(codeBarre) {
        // Afficher le résultat
        const resultDiv = document.getElementById('scanner-result');
        resultDiv.innerHTML = `
            <p style="color: green; font-weight: bold;">✓ Code-barre détecté!</p>
            <p><strong>Code:</strong> ${codeBarre}</p>
            <p>Redirection vers la page de facturation...</p>
        `;
        
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
        const resultDiv = document.getElementById('scanner-result');
        resultDiv.innerHTML = `
            <p style="color: blue;">📱 Code-barre précédent: <strong>${savedCode}</strong></p>
            <button type="button" class="btn" onclick="utiliserCeCode()">Utiliser ce code</button>
            <button type="button" class="btn" onclick="effacerCode()">Effacer</button>
        `;
    }
});

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
