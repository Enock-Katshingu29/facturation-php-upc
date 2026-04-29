📘 Guide d’installation – Application de Facturation
🎯 Objectif

Ce guide explique comment installer et exécuter l’application de facturation en local sur un ordinateur.

🧰 Prérequis

Avant de commencer, assurez-vous d’avoir :

Un serveur local :
Laragon (recommandé)
ou XAMPP / WAMP
PHP 7.4 ou supérieur
Navigateur moderne (Chrome recommandé pour le scanner)
📥 Étape 1 : Récupérer le projet
Option 1 : Télécharger le projet
Extraire le fichier ZIP
Option 2 : Cloner avec Git
git clone https://github.com/ton-username/facturation.git
📂 Étape 2 : Placer le projet

Copier le dossier dans le répertoire du serveur :

Avec Laragon :
C:\laragon\www\facturation
Avec XAMPP :
C:\xampp\htdocs\facturation

▶️ Étape 3 : Lancer le serveur

Démarrer Apache via Laragon / XAMPP
Vérifier que le serveur fonctionne

🌐 Étape 4 : Accéder à l’application

Ouvrir dans le navigateur :

http://localhost/facturation


🔐 Étape 5 : Configuration utilisateur

L’application n’utilise pas de base de données.
Les utilisateurs sont stockés dans :

data/utilisateurs.json
➤ Ajouter un utilisateur

Exemple :

[
  {
    "username": "admin",
    "password": "$2y$10$EXEMPLE_HASH",
    "role": "admin"
  }
]
➤ Générer un mot de passe sécurisé

Créer un fichier PHP temporaire :

<?php
echo password_hash("123456", PASSWORD_DEFAULT);

Puis copier le résultat dans le JSON.

📦 Étape 6 : Vérifier les données

Assurez-vous que ces fichiers existent :

data/produits.json
data/factures.json
data/utilisateurs.json

Sinon, créez-les :

[]
📷 Étape 7 : Tester le scanner

⚠️ Fonctionne uniquement sur navigateur compatible (Chrome)

Autoriser la caméra
Cliquer sur Scanner
Scanner un code-barres
⚠️ Problèmes fréquents & solutions
❌ Erreur : fichier introuvable

✔ Vérifier les chemins (include / require)

❌ "Permission denied"

✔ Vérifier les droits des fichiers JSON

❌ Scanner ne marche pas

✔ Utiliser Google Chrome
✔ Vérifier HTTPS ou localhost

❌ Page blanche

✔ Activer les erreurs PHP :

ini_set('display_errors', 1);
error_reporting(E_ALL);
🔄 Mise à jour du projet

Pour mettre à jour :

git pull
🧪 Tests recommandés
Connexion utilisateur
Ajout produit
Création facture
Scan code-barres
Génération rapport
📌 Remarques importantes
✔ Projet éducatif
✔ Stockage en JSON (pas pour production)
✔ Sécurité à améliorer pour usage réel
👨‍🏫 Conclusion

Ce projet démontre :

La maîtrise de PHP
La gestion des fichiers JSON
La structuration d’une application web
L’intégration d’un scanner code-barres