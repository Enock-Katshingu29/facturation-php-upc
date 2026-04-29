<?php
/**
 * Fonctions de calcul pour la facturation
 * Étudiant 3 : Logic & LaTeX Lead
 */

// Taux de TVA
define('TAUX_TVA', 0.18);

/**
 * Calculer le sous-total HT pour un article
 * @param float $prix_unitaire_ht Prix unitaire hors taxes
 * @param int $quantite Quantité commandée
 * @return float Sous-total HT
 */
function calculerSousTotalHT($prix_unitaire_ht, $quantite) {
    return $prix_unitaire_ht * $quantite;
}

/**
 * Calculer le total HT pour plusieurs articles
 * @param array $articles Tableau d'articles avec prix et quantité
 * @return float Total HT
 */
function calculerTotalHT($articles) {
    $total = 0;
    foreach ($articles as $article) {
        $total += $article['sous_total_ht'];
    }
    return $total;
}

/**
 * Calculer le montant de la TVA
 * @param float $total_ht Total hors taxes
 * @param float $taux Taux de TVA (défaut: 18%)
 * @return float Montant de la TVA
 */
function calculerTVA($total_ht, $taux = TAUX_TVA) {
    return $total_ht * $taux;
}

/**
 * Calculer le total TTC (toutes taxes comprises)
 * @param float $total_ht Total hors taxes
 * @param float $tva Montant de la TVA
 * @return float Total TTC
 */
function calculerTotalTTC($total_ht, $tva) {
    return $total_ht + $tva;
}

/**
 * Formater un prix en CDF avec séparateur de milliers
 * @param float $prix Le prix à formater
 * @return string Prix formaté
 */
function formaterPrix($prix) {
    return number_format($prix, 0, ',', ' ');
}

/**
 * Calculer la monnaie à rendre
 * @param float $montantRecu Montant payé par le client
 * @param float $totalTTC Total à payer
 * @return float Monnaie à rendre
 */
function calculerMonnaie($montantRecu, $totalTTC) {
    $monnaie = $montantRecu - $totalTTC;
    return $monnaie > 0 ? $monnaie : 0;
}