<?php

if (defined("CHARGE_AUTOLOAD")) { // Chargement de l'autoload

    set_autoload();
} else
    die("OUPS Erreur "); // Arrêt en erreur, en mode silence


// fonction de chargement de classe en fonction de la version de php

function set_autoload()
{

    if (PHP_VERSION_ID > 70100) {  // on passe par le gestionaire d'évenement

        function my_autoloader($classname)
        {
            $filename = './class/' . $classname . '.class.php';
            if (file_exists($filename))
                include_once $filename;
            else
                die("Erreur fichier inconnu : " . $filename);
        }

        spl_autoload_register('my_autoloader'); //PHP > 5
    }
}
?>