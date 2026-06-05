<?php
/**
 * Nouvelle Facture - Interface de facturation complète
 * Étudiant 3 : Logic & LaTeX Lead
 */
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");

// Vérifier la permission
exiger_permission("creer_facture");

// Charger les fonctions produits et factures
include_once("../../includes/fonctions-produits.php");
include_once("../../includes/fonctions-factures.php");

$message = "";
$erreur = "";
$factureCreee = null;

// Vérifier si un code-barre a été passé en paramètre (depuis le scanner)
$codeBarreScanne = $_GET['code'] ?? '';

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    if ($_POST["action"] === "ajouter") {
        // Ajouter un article au panier temporaire
        if (!isset($_SESSION["panier"])) {
            $_SESSION["panier"] = [];
        }
        
        $code_barre = $_POST["code_barre"];
        $quantite = (int)$_POST["quantite"];
        
        // Vérifier le produit
        $produit = getProduitParCodeBarre($code_barre);
        if ($produit === null) {
            $erreur = "Produit non trouvé.";
        } elseif (!verifierStock($code_barre, $quantite)) {
            $erreur = "Stock insuffisant. Disponible: " . $produit["quantite_stock"];
        } else {
            // Ajouter au panier
            $_SESSION["panier"][] = [
                "code_barre" => $code_barre,
                "nom" => $produit["nom"],
                "prix_unitaire_ht" => $produit["prix_unitaire_ht"],
                "quantite" => $quantite
            ];
        }
    }
    elseif ($_POST["action"] === "supprimer") {
        $index = (int)$_POST["index"];
        if (isset($_SESSION["panier"][$index])) {
            array_splice($_SESSION["panier"], $index, 1);
        }
    }
    elseif ($_POST["action"] === "valider") {
        // Créer la facture
        if (empty($_SESSION["panier"])) {
            $erreur = "Le panier est vide.";
        } else {
            $articles = [];
            foreach ($_SESSION["panier"] as $item) {
                $articles[] = [
                    "code_barre" => $item["code_barre"],
                    "quantite" => $item["quantite"]
                ];
            }
            
            $result = creerFacture($articles, $_SESSION["identifiant"]);
            
            if ($result[0]) {
                $message = $result[1];
                $factureCreee = $result[2];
                $_SESSION["panier"] = []; // Vider le panier
            } else {
                $erreur = $result[1];
            }
        }
    }
    elseif ($_POST["action"] === "annuler") {
        $_SESSION["panier"] = [];
        $message = "Panier annulé.";
    }
}

// Initialiser le panier si nécessaire
if (!isset($_SESSION["panier"])) {
    $_SESSION["panier"] = [];
}

$produits = chargerProduits();
$codesProduits = array_values(array_map(function($produit) {
    return (string)($produit["code_barre"] ?? "");
}, $produits));
?>

<h2>Nouvelle Facture</h2>

<?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($erreur): ?>
    <p style="color: red;"><?php echo htmlspecialchars($erreur); ?></p>
<?php endif; ?>

<?php if ($factureCreee): ?>
    <!-- Facture créée - affichage du reçu -->
    <div class="facture-recu">
        <h3>Reçu de Facture</h3>
        <p><strong>ID:</strong> <?php echo htmlspecialchars($factureCreee["id_facture"]); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($factureCreee["date"]); ?> à <?php echo htmlspecialchars($factureCreee["heure"]); ?></p>
        <p><strong>Caissier:</strong> <?php echo htmlspecialchars($factureCreee["caissier"]); ?></p>
        
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix Unit.</th>
                    <th>Qté</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factureCreee["articles"] as $article): ?>
                <tr>
                    <td><?php echo htmlspecialchars($article["nom"]); ?></td>
                    <td><?php echo number_format($article["prix_unitaire_ht"], 0, ',', ' '); ?> CDF</td>
                    <td><?php echo $article["quantite"]; ?></td>
                    <td><?php echo number_format($article["sous_total_ht"], 0, ',', ' '); ?> CDF</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p><strong>Total HT:</strong> <?php echo number_format($factureCreee["total_ht"], 0, ',', ' '); ?> CDF</p>
        <p><strong>TVA (18%):</strong> <?php echo number_format($factureCreee["tva"], 0, ',', ' '); ?> CDF</p>
        <p><strong>Total TTC:</strong> <?php echo number_format($factureCreee["total_ttc"], 0, ',', ' '); ?> CDF</p>
        
        <a href="nouvelle-facture.php" class="btn">Nouvelle Facture</a>
    </div>
<?php else: ?>
    <!-- Formulaire d'ajout d'article -->
    <div class="ajout-article">
        <h3>➕ Ajouter un article</h3>
        
        <!-- Bouton pour ouvrir le scanner -->
        <button type="button" class="btn btn-primary" onclick="openScannerForFacture()" style="width: 100%; margin-bottom: 1rem;">
            📷 Scanner un code-barres
        </button>
        
        <form method="post" style="margin-top: 1rem;">
            <input type="hidden" name="action" value="ajouter">
            
            <label for="code_barre">Code-barre du produit:</label>
            <input type="text" name="code_barre" id="code_barre" required placeholder="Entrez ou scannez un code-barre"
                   value="<?php echo htmlspecialchars($codeBarreScanne); ?>">
            
            <label for="quantite">Quantité:</label>
            <input type="number" name="quantite" id="quantite" value="1" min="1" required>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Ajouter au panier</button>
        </form>
    <!-- Panier -->
    <div class="panier">
        <div class="panier-entete">
            <div>
                <h3>Panier en cours</h3>
                <p><?php echo count($_SESSION["panier"]); ?> article(s) ajouté(s)</p>
            </div>
            <span class="panier-badge"><?php echo count($_SESSION["panier"]); ?></span>
        </div>
        
        <?php if (empty($_SESSION["panier"])): ?>
            <div class="panier-vide">
                <p>Aucun article dans le panier.</p>
            </div>
        <?php else: ?>
            <div class="panier-table-wrapper">
                <table class="panier-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-right">Prix Unit.</th>
                            <th class="text-center">Qté</th>
                            <th class="text-right">Sous-total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_ht = 0;
                        foreach ($_SESSION["panier"] as $index => $item):
                            $sous_total = $item["prix_unitaire_ht"] * $item["quantite"];
                            $total_ht += $sous_total;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item["nom"]); ?></strong>
                                <span class="panier-code"><?php echo htmlspecialchars($item["code_barre"]); ?></span>
                            </td>
                            <td class="text-right"><?php echo number_format($item["prix_unitaire_ht"], 0, ',', ' '); ?> CDF</td>
                            <td class="text-center"><span class="quantite-badge"><?php echo $item["quantite"]; ?></span></td>
                            <td class="text-right panier-sous-total"><?php echo number_format($sous_total, 0, ',', ' '); ?> CDF</td>
                            <td class="text-center">
                                <form method="post" class="panier-action-form">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <button type="submit" class="btn-small">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Récapitulatif des totaux -->
            <div class="panier-totaux">
                <p class="total-ht">
                    <span>Total HT:</span>
                    <strong><?php echo number_format($total_ht, 0, ',', ' '); ?> CDF</strong>
                </p>
                <?php
                $tva = $total_ht * 0.18;
                $total_ttc = $total_ht + $tva;
                ?>
                <p class="total-tva">
                    <span>TVA (18%):</span>
                    <strong><?php echo number_format($tva, 0, ',', ' '); ?> CDF</strong>
                </p>
                <p class="total-ttc">
                    <span>Total TTC:</span>
                    <strong><?php echo number_format($total_ttc, 0, ',', ' '); ?> CDF</strong>
                </p>
            </div>
            
            <!-- Boutons d'action -->
            <div class="actions-facture">
                <form method="post">
                    <input type="hidden" name="action" value="valider">
                    <button type="submit" class="btn btn-primary">✓ Valider et créer la facture</button>
                </form>
                
                <form method="post">
                    <input type="hidden" name="action" value="annuler">
                    <button type="submit" class="btn btn-danger">✕ Annuler le panier</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Script du scanner -->
<script src="../../assets/js/scanner.js"></script>
<script>
const codesProduits = <?php echo json_encode($codesProduits, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

function openScannerForFacture() {
    window.Scanner.openScannerModal(function(code) {
        document.getElementById('code_barre').value = code;
        document.getElementById('quantite').focus();
    }, {
        knownCodes: codesProduits,
        requiredReads: 3
    });
}
</script>



<?php include("../../includes/footer.php"); ?>
