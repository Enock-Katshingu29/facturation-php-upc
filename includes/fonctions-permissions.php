<?php

// Matrice des permissions par rôle
$permissions = [
    "caissier" => [
        "dashboard" => true,
        "voir_produits" => true,
        "creer_facture" => true,
        "afficher_facture" => true,
        "rapport_journalier" => true,
        "rapport_mensuel" => false,
        "enregistrer_produit" => false,
        "gestion_produits" => false,
        "gestion_comptes" => false,
        "admin" => false
    ],
    "manager" => [
        "dashboard" => true,
        "voir_produits" => true,
        "creer_facture" => true,
        "afficher_facture" => true,
        "rapport_journalier" => true,
        "rapport_mensuel" => true,
        "enregistrer_produit" => true,
        "gestion_produits" => true,
        "gestion_comptes" => false,
        "admin" => false
    ],
    "super_admin" => [
        "dashboard" => true,
        "voir_produits" => true,
        "creer_facture" => true,
        "afficher_facture" => true,
        "rapport_journalier" => true,
        "rapport_mensuel" => true,
        "enregistrer_produit" => true,
        "gestion_produits" => true,
        "gestion_comptes" => true,
        "admin" => true
    ]
];

/**
 * Vérifier si l'utilisateur a la permission pour une action
 * @param string $action L'action à vérifier
 * @param string $role Le rôle de l'utilisateur (optionnel, utilise la session par défaut)
 * @return bool True si l'utilisateur a la permission
 */
function verifier_permission($action, $role = null) {
    global $permissions;
    
    if ($role === null) {
        if (!isset($_SESSION["role"])) {
            return false;
        }
        $role = $_SESSION["role"];
    }
    
    if (!isset($permissions[$role])) {
        return false;
    }
    
    return isset($permissions[$role][$action]) && $permissions[$role][$action];
}

/**
 * Rediriger vers le dashboard si l'utilisateur n'a pas la permission
 * @param string $action L'action à vérifier
 */
function exiger_permission($action) {
    if (!verifier_permission($action)) {
        header('Location: ../dashboard.php?erreur=acces_refuse');
        exit();
    }
}

/**
 * Vérifier si le lien doit être affiché
 * @param string $action L'action à vérifier
 * @return bool True si le lien doit être affiché
 */
function afficher_lien($action) {
    return verifier_permission($action);
}

?>
