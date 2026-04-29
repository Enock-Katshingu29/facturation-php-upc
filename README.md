🚀 Application de Facturation

📌 Description

Application web de gestion de facturation développée en PHP.
Elle permet de gérer les produits, les factures et les utilisateurs avec un système de rôles et permissions.

💡 Particularité :
👉 Utilise des fichiers JSON au lieu d’une base de données.

🎯 Fonctionnalités

✔ Gestion des utilisateurs (admin / rôles)
✔ Gestion des produits
✔ Création de factures
✔ Calcul automatique des montants
✔ Scanner de code-barres 📷
✔ Rapports (journalier & mensuel)

🖼️ Aperçu du projet

(Ajoute ici des captures d’écran de ton app pour impressionner 👀)

🛠️ Technologies utilisées
⚙️ PHP
🌐 HTML / CSS
⚡ JavaScript
📦 JSON (stockage des données)
📷 API BarcodeDetector
📁 Structure du projet
facturation/
│
├── assets/
├── auth/
├── config/
├── data/
├── includes/
├── modules/
│   ├── admin/
│   ├── facturation/
│   ├── produits/
│
├── rapports/
├── dashboard.php
├── index.php
🔐 Authentification & Sécurité
Système de session
Gestion des rôles
Vérification des permissions :
exiger_permission("nom_permission");
💾 Stockage des données

Les données sont stockées dans :

data/produits.json
data/factures.json
data/utilisateurs.json

✔ Simple et rapide
❌ Non adapté pour les gros systèmes

🚀 Installation
1. Cloner le projet
git clone https://github.com/ton-username/facturation.git
2. Placer dans ton serveur local

Exemple avec Laragon :

C:\laragon\www\facturation
3. Lancer le projet
http://localhost/facturation
🔑 Compte par défaut

Créer un utilisateur dans :

data/utilisateurs.json

Mot de passe hashé :

password_hash("123456", PASSWORD_DEFAULT);
📷 Utilisation du scanner
<script src="assets/js/scanner.js"></script>

<button onclick="scan()">Scanner</button>

<script>
function scan() {
    Scanner.openScannerModal(function(code) {
        window.location.href = "modules/facturation/nouvelle-facture.php?code=" + encodeURIComponent(code);
    });
}
</script>
⚠️ Limitations
Pas de base de données
Sécurité basique
Dépend du navigateur pour le scan
🔧 Améliorations futures
🔄 Migration vers MySQL
📡 API REST
🎨 Interface moderne (React / Vue)
📄 Export PDF
📊 Dashboard avancé
👨‍💻 Auteur

Développé par un étudiant passionné d’informatique 💻

⭐ Contribution

Les contributions sont les bienvenues !

1. Fork
2. Create branch
3. Commit
4. Push
5. Pull Request
📜 Licence

Projet à but éducatif — libre d’utilisation.