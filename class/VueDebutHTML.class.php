<?php
// Fichier : class/VueDebutHTML.class.php
// Début commun à toutes les pages : déclaration XHTML5, <head>, en-tête, menu, ouverture de <main>

class VueDebutHTML
{
    private $titre;  // Titre de la page, différent pour chaque vue

    // Le titre est donné par la vue qui utilise cette classe
    public function __construct($titre = 'Les communes de France')
    {
        $this->titre = $titre;
    }

    public function __toString()
    {
        ob_start();  // Active la mise en mémoire tampon de sortie

        // La déclaration XML est écrite avec echo, jamais en dur dans le gabarit :
        // PHP pourrait prendre "<?xml" pour une balise PHP
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($this->titre) ?></title>
    <link rel="stylesheet" href="css/accueil.css" />
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
        return ob_get_clean();  // Renvoie le contenu du tampon et le vide
    }
}