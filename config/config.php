<?php
// Configuration générale
define("TVA", 0.18);
define("DATA_PRODUITS", __DIR__ . "/../data/produits.json");
define("DATA_FACTURES", __DIR__ . "/../data/factures.json");
define("DATA_UTILISATEURS", __DIR__ . "/../data/utilisateurs.json");
define("BASE_URL", "/facturation");

/**
 * Retourne l'URL de base du projet
 * Fonction centralisée pour générer des URLs absolues valides depuis n'importe quel fichier
 */
function base_url() {
    $base = dirname($_SERVER['SCRIPT_NAME']);
    if ($base === '/' || $base === '\\') {
        $base = '';
    }
    return $base;
}

/**
 * Retourne le chemin absolu vers un fichier depuis la racine du projet
 */
function base_path($path = '') {
    return __DIR__ . '/..' . ($path ? '/' . ltrim($path, '/') : '');
}
