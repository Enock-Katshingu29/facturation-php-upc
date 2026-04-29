<?php
/**
 * Fonctions utilitaires pour la gestion des factures
 * Étudiant 3 : Logic & LaTeX Lead
 */

// Chemin vers le fichier JSON des factures
define('FICHIER_FACTURES', __DIR__ . '/../data/factures.json');

/**
 * Charger toutes les factures depuis le fichier JSON
 * @return array Tableau des factures
 */
function chargerFactures() {
    $contenu = file_get_contents(FICHIER_FACTURES);
    $factures = json_decode($contenu, true);
    
    if ($factures === null) {
        return [];
    }
    
    return $factures;
}

/**
 * Générer un identifiant unique pour une facture
 * @return string Identifiant au format FAC-YYYYMMDD-HHMMSS
 */
function genererIdFacture() {
    return "FAC-" . date("Ymd-His");
}

/**
 * Créer une nouvelle facture avec décrémentation du stock
 * @param array $articles Tableau des articles [code_barre, quantite, prix, nom]
 * @param string $caissier Identifiant du caissier
 * @return array [bool $succes, string $message, array $facture|null]
 */
function creerFacture($articles, $caissier) {
    // Charger les produits pour vérifier le stock
    include_once(__DIR__ . '/fonctions-produits.php');
    
    $articlesValides = [];
    $total_ht = 0;
    
    // Vérifier chaque article
    foreach ($articles as $article) {
        $code_barre = $article['code_barre'];
        $quantite = (int)$article['quantite'];
        
        // Vérifier si le produit existe
        $produit = getProduitParCodeBarre($code_barre);
        if ($produit === null) {
            return [false, "Produit non trouvé: $code_barre", null];
        }
        
        // Vérifier le stock disponible
        if (!verifierStock($code_barre, $quantite)) {
            return [false, "Stock insuffisant pour: " . $produit['nom'], null];
        }
        
        // Calculer le sous-total
        $sous_total = $produit['prix_unitaire_ht'] * $quantite;
        $total_ht += $sous_total;
        
        $articlesValides[] = [
            'code_barre' => $code_barre,
            'nom' => $produit['nom'],
            'prix_unitaire_ht' => $produit['prix_unitaire_ht'],
            'quantite' => $quantite,
            'sous_total_ht' => $sous_total
        ];
        
        // Décrémenter le stock
        decrementerStock($code_barre, $quantite);
    }
    
    // Calculer la TVA (18%) et le total TTC
    $tva = $total_ht * 0.18;
    $total_ttc = $total_ht + $tva;
    
    // Créer la facture
    $facture = [
        'id_facture' => genererIdFacture(),
        'date' => date('Y-m-d'),
        'heure' => date('H:i:s'),
        'caissier' => $caissier,
        'articles' => $articlesValides,
        'total_ht' => $total_ht,
        'tva' => $tva,
        'total_ttc' => $total_ttc
    ];
    
    // Sauvegarder la facture
    $factures = chargerFactures();
    $factures[] = $facture;
    
    if (file_put_contents(FICHIER_FACTURES, json_encode($factures, JSON_PRETTY_PRINT)) === false) {
        return [false, "Erreur lors de l'enregistrement de la facture", null];
    }
    
    return [true, "Facture créée avec succès", $facture];
}

/**
 * Rechercher une facture par son ID
 * @param string $id_facture ID de la facture à rechercher
 * @return array|null La facture trouvée ou null
 */
function getFactureParId($id_facture) {
    $factures = chargerFactures();
    
    foreach ($factures as $facture) {
        if ($facture['id_facture'] === $id_facture) {
            return $facture;
        }
    }
    
    return null;
}

/**
 * Obtenir les factures d'une date spécifique
 * @param string $date Date au format YYYY-MM-DD
 * @return array Tableau des factures du jour
 */
function getFacturesParDate($date) {
    $factures = chargerFactures();
    $facturesJour = [];
    
    foreach ($factures as $facture) {
        if ($facture['date'] === $date) {
            $facturesJour[] = $facture;
        }
    }
    
    return $facturesJour;
}

/**
 * Obtenir les factures d'un mois spécifique
 * @param string $mois Mois au format YYYY-MM
 * @return array Tableau des factures du mois
 */
function getFacturesParMois($mois) {
    $factures = chargerFactures();
    $facturesMois = [];
    
    foreach ($factures as $facture) {
        if (strpos($facture['date'], $mois) === 0) {
            $facturesMois[] = $facture;
        }
    }
    
    return $facturesMois;
}

/**
 * Obtenir les factures d'un caissier spécifique
 * @param string $caissier Identifiant du caissier
 * @return array Tableau des factures du caissier
 */
function getFacturesParCaissier($caissier) {
    $factures = chargerFactures();
    $facturesCaissier = [];
    
    foreach ($factures as $facture) {
        if ($facture['caissier'] === $caissier) {
            $facturesCaissier[] = $facture;
        }
    }
    
    return $facturesCaissier;
}

/**
 * Calculer le chiffre d'affaires pour une période
 * @param string $dateDebut Date de début au format YYYY-MM-DD
 * @param string $dateFin Date de fin au format YYYY-MM-DD
 * @return array [int $ca, int $nbFactures, int $tvaTotal]
 */
function calculerCA($dateDebut, $dateFin) {
    $factures = chargerFactures();
    
    $ca = 0;
    $nbFactures = 0;
    $tvaTotal = 0;
    
    foreach ($factures as $facture) {
        if ($facture['date'] >= $dateDebut && $facture['date'] <= $dateFin) {
            $ca += $facture['total_ttc'];
            $tvaTotal += $facture['tva'];
            $nbFactures++;
        }
    }
    
    return [$ca, $nbFactures, $tvaTotal];
}

/**
 * Obtenir le rapport journalier
 * @param string $date Date au format YYYY-MM-DD (null = aujourd'hui)
 * @return array [int $ca, int $nbFactures, array $factures]
 */
function getRapportJournalier($date = null) {
    if ($date === null) {
        $date = date('Y-m-d');
    }
    
    $factures = getFacturesParDate($date);
    
    $ca = 0;
    foreach ($factures as $facture) {
        $ca += $facture['total_ttc'];
    }
    
    return [
        'date' => $date,
        'ca' => $ca,
        'nb_factures' => count($factures),
        'factures' => $factures
    ];
}

/**
 * Obtenir le rapport mensuel
 * @param string $mois Mois au format YYYY-MM (null = mois actuel)
 * @return array [string $mois, int $ca, int $nbFactures, array $factures]
 */
function getRapportMensuel($mois = null) {
    if ($mois === null) {
        $mois = date('Y-m');
    }
    
    $factures = getFacturesParMois($mois);
    
    $ca = 0;
    foreach ($factures as $facture) {
        $ca += $facture['total_ttc'];
    }
    
    return [
        'mois' => $mois,
        'ca' => $ca,
        'nb_factures' => count($factures),
        'factures' => $factures
    ];
}

/**
 * Annuler une facture (restaurer le stock)
 * @param string $id_facture ID de la facture à annuler
 * @return array [bool $succes, string $message]
 */
function annulerFacture($id_facture) {
    include_once(__DIR__ . '/fonctions-produits.php');
    
    $facture = getFactureParId($id_facture);
    
    if ($facture === null) {
        return [false, "Facture non trouvée"];
    }
    
    // Restaurer le stock pour chaque article
    foreach ($facture['articles'] as $article) {
        incrementerStock($article['code_barre'], $article['quantite']);
    }
    
    // Supprimer la facture
    $factures = chargerFactures();
    $nouvellesFactures = array_filter($factures, function($f) use ($id_facture) {
        return $f['id_facture'] !== $id_facture;
    });
    
    if (file_put_contents(FICHIER_FACTURES, json_encode(array_values($nouvellesFactures), JSON_PRETTY_PRINT)) === false) {
        return [false, "Erreur lors de l'annulation de la facture"];
    }
    
    return [true, "Facture annulée et stock restauré"];
}

/**
 * Obtenir les statistiques des produits les plus vendus
 * @param int $limite Nombre de produits à retourner
 * @return array Tableau des produits les plus vendus
 */
function getProduitsLesPlusVendus($limite = 10) {
    $factures = chargerFactures();
    $ventes = [];
    
    // Compter les ventes par produit
    foreach ($factures as $facture) {
        foreach ($facture['articles'] as $article) {
            $code_barre = $article['code_barre'];
            
            if (!isset($ventes[$code_barre])) {
                $ventes[$code_barre] = [
                    'code_barre' => $code_barre,
                    'nom' => $article['nom'],
                    'quantite_vendue' => 0,
                    'chiffre_affaires' => 0
                ];
            }
            
            $ventes[$code_barre]['quantite_vendue'] += $article['quantite'];
            $ventes[$code_barre]['chiffre_affaires'] += $article['sous_total_ht'];
        }
    }
    
    // Trier par quantité vendues
    usort($ventes, function($a, $b) {
        return $b['quantite_vendue'] - $a['quantite_vendue'];
    });
    
    return array_slice($ventes, 0, $limite);
}