<?php
// Configuration générale
defined("TVA") || define("TVA", 0.18);
defined("DATA_PRODUITS") || define("DATA_PRODUITS", __DIR__ . "/../data/produits.json");
defined("DATA_FACTURES") || define("DATA_FACTURES", __DIR__ . "/../data/factures.json");
defined("DATA_UTILISATEURS") || define("DATA_UTILISATEURS", __DIR__ . "/../data/utilisateurs.json");
defined("BASE_URL") || define("BASE_URL", "/facturation");

/**
 * Retourne l'URL de base du projet
 * Fonction centralisée pour générer des URLs absolues valides depuis n'importe quel fichier
 */
if (!function_exists("base_url")) {
    function base_url() {
        $base = dirname($_SERVER['SCRIPT_NAME']);
        if ($base === '/' || $base === '\\') {
            $base = '';
        }
        return $base;
    }
}

/**
 * Retourne le chemin absolu vers un fichier depuis la racine du projet
 */
if (!function_exists("base_path")) {
    function base_path($path = '') {
        return __DIR__ . '/..' . ($path ? '/' . ltrim($path, '/') : '');
    }
}
