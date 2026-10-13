<?php
// Fichier : class/VueAccueil.class.php
// Vue de la page d'accueil : elle interroge le modèle et affiche les statistiques

class VueAccueil
{
    private $modele;  // Le modèle reçu du contrôleur

    public function __construct(Communes $modele)
    {
        $this->modele = $modele;
    }

    public function __toString()
    {
        // Questions posées au modèle grâce à ses accesseurs
        $total  = $this->modele->getNombreTotal();
        $region = $this->modele->getRegionMaxCommunes();
        $dep    = $this->modele->getDepartementMinCommunes();

        // Mise en forme : 34868 devient "34 868"
        $totalAffiche = number_format($total, 0, ',', ' ');
        $regionNombre = number_format($region['nombre'], 0, ',', ' ');

        // Contenu propre à cette page : les trois cartes
        $contenu  = '        <section class="cartes">' . "\n";

        $contenu .= '            <div class="carte">' . "\n";
        $contenu .= '                <p class="chiffre">' . $totalAffiche . '</p>' . "\n";
        $contenu .= '                <p>communes en France</p>' . "\n";
        $contenu .= '            </div>' . "\n";

        $contenu .= '            <div class="carte">' . "\n";
        $contenu .= '                <p class="chiffre">' . htmlspecialchars($region['nom']) . '</p>' . "\n";
        $contenu .= '                <p>région avec le plus de communes (' . $regionNombre . ')</p>' . "\n";
        $contenu .= '            </div>' . "\n";

        $contenu .= '            <div class="carte">' . "\n";
        $contenu .= '                <p class="chiffre">' . htmlspecialchars($dep['nom']) . '</p>' . "\n";
        $contenu .= '                <p>département avec le moins de communes (' . $dep['nombre'] . ')</p>' . "\n";
        $contenu .= '            </div>' . "\n";

        $contenu .= '        </section>' . "\n";

        // La page = début commun + contenu de cette vue + fin commune
        $debut = new VueDebutHTML('Accueil - Les communes de France', ['accueil.css']);
        $fin   = new VueFinHTML();

        return $debut . $contenu . $fin;
    }
}