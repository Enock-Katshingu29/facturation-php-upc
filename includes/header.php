<?php
include_once(__DIR__ . "/../config/config.php");
include_once(__DIR__ . "/fonctions-permissions.php");

$currentPath = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH);
$navClass = static function (string $path) use ($currentPath): string {
    return substr($currentPath, -strlen($path)) === $path ? ' class="active"' : "";
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Système de Facturation</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <a class="brand" href="<?php echo BASE_URL; ?>/dashboard.php">
        <span class="brand-mark">K</span>
        <span><strong>KIMA Caisse</strong><small>Gestion du supermarché</small></span>
      </a>
      <nav aria-label="Navigation principale">
        <ul>
        <li><a<?php echo $navClass("/dashboard.php"); ?> href="<?php echo BASE_URL; ?>/dashboard.php">Accueil</a></li>

        <?php if (afficher_lien("creer_facture")): ?>
          <li><a<?php echo $navClass("/modules/facturation/nouvelle-facture.php"); ?> href="<?php echo BASE_URL; ?>/modules/facturation/nouvelle-facture.php">Nouvelle facture</a></li>
        <?php endif; ?>
        
        <?php if (afficher_lien("voir_produits")): ?>
          <li><a<?php echo $navClass("/modules/produits/lire.php"); ?> href="<?php echo BASE_URL; ?>/modules/produits/lire.php">Produits</a></li>
        <?php endif; ?>
        
        <?php if (afficher_lien("gestion_produits")): ?>
          <li><a<?php echo $navClass("/modules/produits/enregistrer.php"); ?> href="<?php echo BASE_URL; ?>/modules/produits/enregistrer.php">Stock</a></li>
        <?php endif; ?>

        <?php if (afficher_lien("rapport_journalier")): ?>
          <li><a<?php echo $navClass("/rapports/rapport-journalier.php"); ?> href="<?php echo BASE_URL; ?>/rapports/rapport-journalier.php">Rapport journalier</a></li>
        <?php endif; ?>
        
        <?php if (afficher_lien("rapport_mensuel")): ?>
          <li><a<?php echo $navClass("/rapports/rapport-mensuel.php"); ?> href="<?php echo BASE_URL; ?>/rapports/rapport-mensuel.php">Rapport mensuel</a></li>
        <?php endif; ?>

        <?php if (afficher_lien("gestion_comptes")): ?>
          <li><a<?php echo $navClass("/modules/admin/gestion-comptes.php"); ?> href="<?php echo BASE_URL; ?>/modules/admin/gestion-comptes.php">Comptes</a></li>
        <?php endif; ?>

        <li><a class="nav-logout" href="<?php echo BASE_URL; ?>/auth/logout.php">Déconnexion</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <main class="site-main">
