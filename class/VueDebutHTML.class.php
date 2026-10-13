<?php
// Fichier : class/VueDebutHTML.class.php
// Début commun à toutes les pages : déclaration XHTML5, <head>, en-tête, menu, ouverture de <main>

class VueDebutHTML
{
    private $titre;  // Titre de la page, différent pour chaque vue
    private $css;    // Liste des fichiers CSS propres à la vue

    // La vue donne son titre et, si besoin, ses fichiers CSS
    public function __construct($titre = 'Les communes de France', $css = [])
    {
        $this->titre = $titre;
        $this->css = $css;
    }

    public function __toString()
    {
        ob_start();

        // La déclaration XML est écrite avec echo : PHP pourrait prendre "<?xml" pour une balise PHP
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($this->titre) ?></title>
<?php foreach ($this->css as $fichier) { ?>
    <link rel="stylesheet" href="css/<?= htmlspecialchars($fichier) ?>" />
<?php } ?>
</head>
<body>
<div class="page">
    <header class="entete">
        <h1>Les communes de France</h1>
        <nav class="menu">
            <a href="index.php?cmd=accueil">Accueil</a>
            <a href="index.php?cmd=communes">Toutes les communes</a>
            <a href="index.php?cmd=departements">Départements</a>
            <a href="index.php?cmd=regions">Régions</a>
        </nav>
    </header>
    <main>
<?php
        return ob_get_clean();
    }
}