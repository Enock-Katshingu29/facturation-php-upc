<?php
/**
 * Fonctions utilitaires pour la gestion des produits
 * Étudiant 1 : Lead Backend & Data
 */

// Chemin vers le fichier JSON des produits
define('FICHIER_PRODUITS', __DIR__ . '/../data/produits.json');

/**
 * Charger tous les produits depuis le fichier JSON
 * @return array Tableau des produits
 */
function chargerProduits() {
    $contenu = file_get_contents(FICHIER_PRODUITS);
    $produits = json_decode($contenu, true);
    
    if ($produits === null) {
        return [];
    }
    
    return $produits;
}

/**
 * Rechercher un produit par son code-barre
 * @param string $code_barre Le code-barre à rechercher
 * @return array|null Le produit trouvé ou null
 */
function getProduitParCodeBarre($code_barre) {
    $produits = chargerProduits();
    
    foreach ($produits as $produit) {
        if ($produit['code_barre'] === $code_barre) {
            return $produit;
        }
    }
    
    return null;
}

/**
 * Ajouter un nouveau produit
 * @param array $produit Données du produit à ajouter
 * @return bool True si succès, False sinon
 */
function ajouterProduit($produit) {
    $produits = chargerProduits();
    
    // Vérifier si le produit existe déjà
    foreach ($produits as $p) {
        if ($p['code_barre'] === $produit['code_barre']) {
            return false; // Produit déjà existant
        }
    }
    
    // Ajouter la date d'enregistrement si non spécifiée
    if (!isset($produit['date_enregistrement'])) {
        $produit['date_enregistrement'] = date('Y-m-d');
    }
    
    $produits[] = $produit;
    
    return file_put_contents(FICHIER_PRODUITS, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Mettre à jour un produit existant
 * @param string $code_barre Code-barre du produit à modifier
 * @param array $nouvellesDonnees Nouvelles données du produit
 * @return bool True si succès, False sinon
 */
function modifierProduit($code_barre, $nouvellesDonnees) {
    $produits = chargerProduits();
    
    foreach ($produits as &$produit) {
        if ($produit['code_barre'] === $code_barre) {
            $produit = array_merge($produit, $nouvellesDonnees);
            return file_put_contents(FICHIER_PRODUITS, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
        }
    }
    
    return false;
}

/**
 * Supprimer un produit par son code-barre
 * @param string $code_barre Code-barre du produit à supprimer
 * @return bool True si succès, False sinon
 */
function supprimerProduit($code_barre) {
    $produits = chargerProduits();
    
    $nouveauxProduits = array_filter($produits, function($p) use ($code_barre) {
        return $p['code_barre'] !== $code_barre;
    });
    
    if (count($nouveauxProduits) === count($produits)) {
        return false; // Produit non trouvé
    }
    
    return file_put_contents(FICHIER_PRODUITS, json_encode(array_values($nouveauxProduits), JSON_PRETTY_PRINT)) !== false;
}

/**
 * Vérifier la disponibilité du produit en stock
 * @param string $code_barre Code-barre du produit
 * @param int $quantite Quantité demandée
 * @return bool True si disponible en quantité suffisante
 */
function verifierStock($code_barre, $quantite) {
    $produit = getProduitParCodeBarre($code_barre);
    
    if ($produit === null) {
        return false;
    }
    
    return $produit['quantite_stock'] >= $quantite;
}

/**
 * Décrémenter le stock d'un produit
 * @param string $code_barre Code-barre du produit
 * @param int $quantite Quantité à retirer
 * @return bool True si succès, False sinon
 */
function decrementerStock($code_barre, $quantite) {
    $produits = chargerProduits();
    
    foreach ($produits as &$produit) {
        if ($produit['code_barre'] === $code_barre) {
            $nouveauStock = $produit['quantite_stock'] - $quantite;
            
            if ($nouveauStock < 0) {
                return false; // Stock insuffisant
            }
            
            $produit['quantite_stock'] = $nouveauStock;
            return file_put_contents(FICHIER_PRODUITS, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
        }
    }
    
    return false;
}

/**
 * Incrémenter le stock d'un produit
 * @param string $code_barre Code-barre du produit
 * @param int $quantite Quantité à ajouter
 * @return bool True si succès, False sinon
 */
function incrementerStock($code_barre, $quantite) {
    $produits = chargerProduits();
    
    foreach ($produits as &$produit) {
        if ($produit['code_barre'] === $code_barre) {
            $produit['quantite_stock'] += $quantite;
            return file_put_contents(FICHIER_PRODUITS, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
        }
    }
    
    return false;
}

/**
 * Obtenir la liste des produits avec stock faible
 * @param int $seuil Seuil minimal de stock
 * @return array Tableau des produits avec stock faible
 */
function getProduitsStockFaible($seuil = 10) {
    $produits = chargerProduits();
    $produitsFaibles = [];
    
    foreach ($produits as $produit) {
        if ($produit['quantite_stock'] <= $seuil) {
            $produitsFaibles[] = $produit;
        }
    }
    
    return $produitsFaibles;
}

/**
 * Valider les données d'un produit
 * @param array $produit Données du produit à valider
 * @return array [bool $estValide, string $messageErreur]
 */
function validerProduit($produit) {
    $erreurs = [];
    
    // Vérifier le code-barre
    if (empty($produit['code_barre'])) {
        $erreurs[] = "Le code-barre est obligatoire";
    } elseif (strlen($produit['code_barre']) < 8) {
        $erreurs[] = "Le code-barre doit contenir au moins 8 caractères";
    }
    
    // Vérifier le nom
    if (empty($produit['nom'])) {
        $erreurs[] = "Le nom du produit est obligatoire";
    }
    
    // Vérifier le prix
    if (!isset($produit['prix_unitaire_ht']) || $produit['prix_unitaire_ht'] <= 0) {
        $erreurs[] = "Le prix unitaire doit être supérieur à 0";
    }
    
    // Vérifier la quantité
    if (!isset($produit['quantite_stock']) || $produit['quantite_stock'] < 0) {
        $erreurs[] = "La quantité en stock doit être positive";
    }
    
    // Vérifier la date d'expiration
    if (!empty($produit['date_expiration'])) {
        $dateExp = strtotime($produit['date_expiration']);
        $aujourdhui = strtotime(date('Y-m-d'));
        
        if ($dateExp < $aujourdhui) {
            $erreurs[] = "La date d'expiration ne peut pas être dans le passé";
        }
    }
    
    if (empty($erreurs)) {
        return [true, ""];
    }
    
    return [false, implode(". ", $erreurs)];
}