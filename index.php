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
$action = 'accueil'; // Valeur par défaut, utilisée si l'URL ne contient pas ?cmd=
if (isset($_GET['cmd']) && !empty($_GET['cmd'])) {
    $action = $_GET['cmd'];
}



// Aiguillage des vues : chaque vue reçoit le modèle
switch ($action) {

// Les autres vues seront ajoutées ici (communes, departements, regions)
    case "accueil":
        $vue = new VueAccueil($modele);
        echo $vue;
        break;

    case "communes":
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;   // le contrôleur lit
        $recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
        $vue = new VueCommunes($modele, $page, $recherche); // et donne le numéro à la vue    
        echo $vue;
        break;

    case "departements":
        // Le contrôleur lit le code du département dans l'URL (vide = liste des départements)
        $dep = isset($_GET['dep']) ? trim($_GET['dep']) : '';
        $vue = new VueDepartements($modele, $dep);
        echo $vue;
        break;

    case "regions":
        // Le contrôleur lit le nom de la région dans l'URL (vide = liste des régions)
        $region = isset($_GET['region']) ? trim($_GET['region']) : '';
        $vue = new VueRegions($modele, $region);
        echo $vue;
        break;
        
    default:
        // Commande inconnue : on retombe sur l'accueil
        $vue = new VueAccueil($modele);
        echo $vue;
        break;
}
        
