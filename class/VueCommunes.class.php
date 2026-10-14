<?php
// Fichier : class/VueCommunes.class.php
// Vue de la liste des communes, avec barre de recherche et pagination

class VueCommunes
{
    private $modele;          // Le modèle reçu du contrôleur
    private $page;            // Numéro de la page affichée
    private $recherche;       // Texte recherché ("" = pas de filtre)
    private $parPage = 100;   // Nombre de communes par page

    public function __construct(Communes $modele, $page = 1, $recherche = '')
    {
        $this->modele = $modele;
        $this->recherche = trim($recherche);

        // Une page inexistante est ramenée à la première ou à la dernière page
        $nbPages = $this->modele->getNombrePages($this->parPage, $this->recherche);
        $this->page = min(max(1, (int)$page), $nbPages);
    }

    public function __toString()
    {
        // Questions posées au modèle (toutes tiennent compte de la recherche)
        $trouvees = $this->modele->getNombreCommunesRecherche($this->recherche);
        $nbPages  = $this->modele->getNombrePages($this->parPage, $this->recherche);
        $communes = $this->modele->getListeCommunesPage($this->page, $this->parPage, $this->recherche);

        // Titre et formulaire de recherche
        $contenu  = '        <section class="liste">' . "\n";
        $contenu .= '            <h2>Toutes les communes</h2>' . "\n";
        $contenu .= $this->formulaireRecherche();

        // Résumé : il change selon qu'on a recherché quelque chose ou non
        if ($this->recherche === '') {
            $contenu .= '            <p>' . number_format($trouvees, 0, ',', ' ') . ' communes - page '
                      . $this->page . ' sur ' . $nbPages . '</p>' . "\n";
        } elseif ($trouvees === 0) {
            $contenu .= '            <p>Aucune commune trouvée pour « ' . htmlspecialchars($this->recherche) . ' ».</p>' . "\n";
        } else {
            $contenu .= '            <p>' . number_format($trouvees, 0, ',', ' ') . ' commune(s) trouvée(s) pour « '
                      . htmlspecialchars($this->recherche) . ' » - page ' . $this->page . ' sur ' . $nbPages . '</p>' . "\n";
        }

        // Tableau : une ligne par commune (seulement s'il y a des résultats)
        if ($trouvees > 0) {
            $contenu .= '            <div class="defilement">' . "\n";
            $contenu .= '            <table class="tableau">' . "\n";
            $contenu .= '                <thead>' . "\n";
            $contenu .= '                    <tr><th>Commune</th><th>Département</th><th>Région</th></tr>' . "\n";
            $contenu .= '                </thead>' . "\n";
            $contenu .= '                <tbody>' . "\n";

            foreach ($communes as $c) {
                // Code du département sur 2 caractères pour l'affichage : "1" devient "01", "2A" reste "2A"
                $codeDep = str_pad($c['dep_code'], 2, '0', STR_PAD_LEFT);

                $contenu .= '                    <tr>'
                          . '<td>' . htmlspecialchars($c['nom']) . '</td>'
                          . '<td>' . $codeDep . ' - ' . htmlspecialchars($c['dep_nom']) . '</td>'
                          . '<td>' . htmlspecialchars($c['reg_nom']) . '</td>'
                          . '</tr>' . "\n";
            }

            $contenu .= '                </tbody>' . "\n";
            $contenu .= '            </table>' . "\n";
            $contenu .= '            </div>' . "\n";

            // Liens de pagination
            $contenu .= $this->lienPagination($nbPages);
        }

        $contenu .= '        </section>' . "\n";

        // La page = début commun + contenu de cette vue + fin commune
        // Le second paramètre indique le CSS propre à cette vue (dans le dossier css/)
        $debut = new VueDebutHTML('Communes - Les communes de France', ['communes.css']);
        $fin   = new VueFinHTML();

        return $debut . $contenu . $fin;
    }

    // Fabrique le formulaire "Rechercher votre commune"
    // Il envoie la recherche dans l'URL (method="get") : ?cmd=communes&recherche=...
    private function formulaireRecherche()
    {
        $h  = '            <form class="recherche" method="get" action="index.php">' . "\n";
        $h .= '                <input type="hidden" name="cmd" value="communes" />' . "\n";
        $h .= '                <label for="recherche">Rechercher votre commune :</label>' . "\n";
        $h .= '                <input type="text" id="recherche" name="recherche" value="'
            . htmlspecialchars($this->recherche) . '" placeholder="Ex : Saint-Étienne" />' . "\n";
        $h .= '                <button type="submit">Rechercher</button>' . "\n";

        // Lien pour revenir à la liste complète quand une recherche est active
        if ($this->recherche !== '') {
            $h .= '                <a href="index.php?cmd=communes">Effacer la recherche</a>' . "\n";
        }

        $h .= '            </form>' . "\n";

        return $h;
    }

    // Fabrique les liens Première / Précédente / Suivante / Dernière
    // Elle renvoie uniquement les liens, jamais l'en-tête ni le pied de page
    private function lienPagination($nbPages)
    {
        // La recherche est gardée dans les liens, sinon le filtre disparaîtrait à la page 2
        // (urlencode protège les espaces et les accents ; en XHTML, le & s'écrit &amp;)
        $base = 'index.php?cmd=communes';
        if ($this->recherche !== '') {
            $base .= '&amp;recherche=' . urlencode($this->recherche);
        }
        $base .= '&amp;page=';

        $h = '            <nav class="pagination">' . "\n";

        if ($this->page > 1) {
            $h .= '                <a href="' . $base . '1">Première</a>' . "\n";
            $h .= '                <a href="' . $base . ($this->page - 1) . '">Précédente</a>' . "\n";
        }

        $h .= '                <span>Page ' . $this->page . ' / ' . $nbPages . '</span>' . "\n";

        if ($this->page < $nbPages) {
            $h .= '                <a href="' . $base . ($this->page + 1) . '">Suivante</a>' . "\n";
            $h .= '                <a href="' . $base . $nbPages . '">Dernière</a>' . "\n";
        }

        $h .= '            </nav>' . "\n";

        return $h;
    }
}