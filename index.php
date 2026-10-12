<?php
// index.php - Point d'entrée principal du projet

// Activer les erreurs en développement
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

// Page servie en XHTML5 avec l'encodage UTF-8
header("Content-Type: application/xhtml+xml; charset=utf-8");

// Charger l'autoload
define("CHARGE_AUTOLOAD", true);
require_once("inc/poo.inc.php");

// Le contrôleur crée le modèle et le garde dans une variable
$modele = new Communes();

// Router principal : "accueil" si aucune commande n'est donnée

if (isset($_GET['cmd']) && !empty($_GET['cmd'])) {
    $action = $_GET['cmd'];
}

$action = 'accueil'; // Valeur par défaut, utilisée si l'URL ne contient pas ?cmd=

// Aiguillage des vues : chaque vue reçoit le modèle
switch ($action) {
    case "accueil":
        $vue = new VueAccueil($modele);
        echo $vue;
        break;

    // Les autres vues seront ajoutées ici (communes, departements, regions)

    default:
        // Commande inconnue : on retombe sur l'accueil
        $vue = new VueAccueil($modele);
        echo $vue;
        break;
}