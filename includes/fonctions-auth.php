<?php
/**
 * Fonctions utilitaires pour l'authentification
 * Étudiant 1 : Lead Backend & Data
 */

// Chemin vers le fichier JSON des utilisateurs
define('FICHIER_UTILISATEURS', __DIR__ . '/../data/utilisateurs.json');

/**
 * Charger tous les utilisateurs depuis le fichier JSON
 * @return array Tableau des utilisateurs
 */
function chargerUtilisateurs() {
    $contenu = file_get_contents(FICHIER_UTILISATEURS);
    $utilisateurs = json_decode($contenu, true);
    
    if ($utilisateurs === null) {
        return [];
    }
    
    return $utilisateurs;
}

/**
 * Rechercher un utilisateur par son identifiant
 * @param string $identifiant L'identifiant à rechercher
 * @return array|null L'utilisateur trouvé ou null
 */
function getUtilisateurParIdentifiant($identifiant) {
    $utilisateurs = chargerUtilisateurs();
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['identifiant'] === $identifiant) {
            return $utilisateur;
        }
    }
    
    return null;
}

/**
 * Rechercher un utilisateur par son nom complet
 * @param string $nom_complet Le nom complet à rechercher
 * @return array|null L'utilisateur trouvé ou null
 */
function getUtilisateurParNom($nom_complet) {
    $utilisateurs = chargerUtilisateurs();
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['nom_complet'] === $nom_complet) {
            return $utilisateur;
        }
    }
    
    return null;
}

/**
 * Vérifier les identifiants de connexion
 * @param string $nom Nom complet de l'utilisateur
 * @param string $role Rôle de l'utilisateur
 * @param string $motDePasse Mot de passe en clair
 * @return array|null L'utilisateur si authentifié, null sinon
 */
function verifierConnexion($nom, $role, $motDePasse) {
    $utilisateurs = chargerUtilisateurs();
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['nom_complet'] === $nom 
            && $utilisateur['role'] === $role 
            && $utilisateur['actif'] 
            && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            return $utilisateur;
        }
    }
    
    return null;
}

/**
 * Créer un nouvel utilisateur
 * @param string $identifiant Identifiant unique
 * @param string $motDePasse Mot de passe en clair
 * @param string $role Rôle (caissier, manager, super_admin)
 * @param string $nomComplet Nom complet de l'utilisateur
 * @return array [bool $succes, string $message]
 */
function creerUtilisateur($identifiant, $motDePasse, $role, $nomComplet) {
    $utilisateurs = chargerUtilisateurs();
    
    // Vérifier si l'identifiant existe déjà
    foreach ($utilisateurs as $u) {
        if ($u['identifiant'] === $identifiant) {
            return [false, "Cet identifiant existe déjà"];
        }
    }
    
    // Vérifier le rôle
    $rolesValides = ['caissier', 'manager', 'super_admin'];
    if (!in_array($role, $rolesValides)) {
        return [false, "Rôle invalide"];
    }
    
    $nouvelUtilisateur = [
        'identifiant' => $identifiant,
        'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
        'role' => $role,
        'nom_complet' => $nomComplet,
        'date_creation' => date('Y-m-d'),
        'actif' => true
    ];
    
    $utilisateurs[] = $nouvelUtilisateur;
    
    if (file_put_contents(FICHIER_UTILISATEURS, json_encode($utilisateurs, JSON_PRETTY_PRINT)) === false) {
        return [false, "Erreur lors de la création de l'utilisateur"];
    }
    
    return [true, "Utilisateur créé avec succès"];
}

/**
 * Modifier un utilisateur existant
 * @param string $identifiant Identifiant de l'utilisateur à modifier
 * @param array $nouvellesDonnees Nouvelles données [nom_complet, role, actif, mot_de_passe]
 * @return array [bool $succes, string $message]
 */
function modifierUtilisateur($identifiant, $nouvellesDonnees) {
    $utilisateurs = chargerUtilisateurs();
    
    foreach ($utilisateurs as &$utilisateur) {
        if ($utilisateur['identifiant'] === $identifiant) {
            // Mettre à jour le nom complet
            if (isset($nouvellesDonnees['nom_complet'])) {
                $utilisateur['nom_complet'] = $nouvellesDonnees['nom_complet'];
            }
            
            // Mettre à jour le rôle
            if (isset($nouvellesDonnees['role'])) {
                $rolesValides = ['caissier', 'manager', 'super_admin'];
                if (!in_array($nouvellesDonnees['role'], $rolesValides)) {
                    return [false, "Rôle invalide"];
                }
                $utilisateur['role'] = $nouvellesDonnees['role'];
            }
            
            // Mettre à jour le statut actif
            if (isset($nouvellesDonnees['actif'])) {
                $utilisateur['actif'] = (bool)$nouvellesDonnees['actif'];
            }
            
            // Mettre à jour le mot de passe
            if (isset($nouvellesDonnees['mot_de_passe']) && !empty($nouvellesDonnees['mot_de_passe'])) {
                $utilisateur['mot_de_passe'] = password_hash($nouvellesDonnees['mot_de_passe'], PASSWORD_DEFAULT);
            }
            
            if (file_put_contents(FICHIER_UTILISATEURS, json_encode($utilisateurs, JSON_PRETTY_PRINT)) === false) {
                return [false, "Erreur lors de la modification de l'utilisateur"];
            }
            
            return [true, "Utilisateur modifié avec succès"];
        }
    }
    
    return [false, "Utilisateur non trouvé"];
}

/**
 * Supprimer un utilisateur
 * @param string $identifiant Identifiant de l'utilisateur à supprimer
 * @return array [bool $succes, string $message]
 */
function supprimerUtilisateur($identifiant) {
    $utilisateurs = chargerUtilisateurs();
    
    $nouveauxUtilisateurs = array_filter($utilisateurs, function($u) use ($identifiant) {
        return $u['identifiant'] !== $identifiant;
    });
    
    if (count($nouveauxUtilisateurs) === count($utilisateurs)) {
        return [false, "Utilisateur non trouvé"];
    }
    
    if (file_put_contents(FICHIER_UTILISATEURS, json_encode(array_values($nouveauxUtilisateurs), JSON_PRETTY_PRINT)) === false) {
        return [false, "Erreur lors de la suppression de l'utilisateur"];
    }
    
    return [true, "Utilisateur supprimé avec succès"];
}

/**
 * Désactiver un utilisateur (sans le supprimer)
 * @param string $identifiant Identifiant de l'utilisateur à désactiver
 * @return array [bool $succes, string $message]
 */
function desactiverUtilisateur($identifiant) {
    return modifierUtilisateur($identifiant, ['actif' => false]);
}

/**
 * Activer un utilisateur
 * @param string $identifiant Identifiant de l'utilisateur à activer
 * @return array [bool $succes, string $message]
 */
function reactiverUtilisateur($identifiant) {
    return modifierUtilisateur($identifiant, ['actif' => true]);
}

/**
 * Vérifier si un utilisateur est connecté
 * @return bool True si connecté, False sinon
 */
function estConnecte() {
    return isset($_SESSION['identifiant']) && isset($_SESSION['role']);
}

/**
 * Obtenir l'utilisateur actuellement connecté
 * @return array|null Les données de l'utilisateur ou null
 */
function getUtilisateurConnecte() {
    if (estConnecte()) {
        return $_SESSION['utilisateur'] ?? null;
    }
    return null;
}

/**
 * Obtenir le rôle de l'utilisateur connecté
 * @return string|null Le rôle ou null
 */
function getRoleUtilisateur() {
    return $_SESSION['role'] ?? null;
}

/**
 * Vérifier si l'utilisateur a un rôle spécifique
 * @param string $role Le rôle à vérifier
 * @return bool True si l'utilisateur a ce rôle
 */
function aLeRole($role) {
    return getRoleUtilisateur() === $role;
}

/**
 * Déconnecter l'utilisateur
 */
function deconnecter() {
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire la session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Changer le mot de passe d'un utilisateur
 * @param string $identifiant Identifiant de l'utilisateur
 * @param string $ancienMotDePasse Ancien mot de passe (pour vérification)
 * @param string $nouveauMotDePasse Nouveau mot de passe
 * @return array [bool $succes, string $message]
 */
function changerMotDePasse($identifiant, $ancienMotDePasse, $nouveauMotDePasse) {
    $utilisateur = getUtilisateurParIdentifiant($identifiant);
    
    if ($utilisateur === null) {
        return [false, "Utilisateur non trouvé"];
    }
    
    // Vérifier l'ancien mot de passe
    if (!password_verify($ancienMotDePasse, $utilisateur['mot_de_passe'])) {
        return [false, "Ancien mot de passe incorrect"];
    }
    
    return modifierUtilisateur($identifiant, ['mot_de_passe' => $nouveauMotDePasse]);
}

/**
 * Réinitialiser le mot de passe d'un utilisateur (admin uniquement)
 * @param string $identifiant Identifiant de l'utilisateur
 * @param string $nouveauMotDePasse Nouveau mot de passe
 * @return array [bool $succes, string $message]
 */
function reinitialiserMotDePasse($identifiant, $nouveauMotDePasse) {
    return modifierUtilisateur($identifiant, ['mot_de_passe' => $nouveauMotDePasse]);
}

/**
 * Obtenir la liste des utilisateurs par rôle
 * @param string $role Le rôle à filtrer
 * @return array Tableau des utilisateurs avec ce rôle
 */
function getUtilisateursParRole($role) {
    $utilisateurs = chargerUtilisateurs();
    $result = [];
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['role'] === $role) {
            $result[] = $utilisateur;
        }
    }
    
    return $result;
}

/**
 * Obtenir la liste des utilisateurs actifs
 * @return array Tableau des utilisateurs actifs
 */
function getUtilisateursActifs() {
    $utilisateurs = chargerUtilisateurs();
    $result = [];
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['actif']) {
            $result[] = $utilisateur;
        }
    }
    
    return $result;
}