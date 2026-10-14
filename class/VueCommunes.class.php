<?php
// Fichier : class/VueCommunes.class.php
// Vue de la liste de toutes les communes, avec pagination

class VueCommunes
{
    private $modele;          // Le modèle reçu du contrôleur
    private $page;            // Numéro de la page affichée
    private $parPage = 100;   // Nombre de communes par page

    public function __construct(Communes $modele, $page = 1)
    {
        $this->modele = $modele;

        // Une page inexistante est ramenée à la première ou à la dernière page
        $nbPages = $this->modele->getNombrePages($this->parPage);
        $this->page = min(max(1, (int)$page), $nbPages);
    }

    public function __toString()
    {
        // Questions posées au modèle
        $total    = $this->modele->getNombreTotal();
        $nbPages  = $this->modele->getNombrePages($this->parPage);
        $communes = $this->modele->getListeCommunesPage($this->page, $this->parPage);

        // Titre et résumé
        $contenu  = '        <section class="liste">' . "\n";
        $contenu .= '            <h2>Toutes les communes</h2>' . "\n";
        $contenu .= '            <p>' . number_format($total, 0, ',', ' ') . ' communes - page '
                  . $this->page . ' sur ' . $nbPages . '</p>' . "\n";

        // Tableau : une ligne par commune
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

        $contenu .= '        </section>' . "\n";

        // La page = début commun + contenu de cette vue + fin commune
        // Le second paramètre indique le CSS propre à cette vue (dans le dossier css/)
        $debut = new VueDebutHTML('Communes - Les communes de France', ['communes.css']);
        $fin   = new VueFinHTML();

        return $debut . $contenu . $fin;
    }

    // Fabrique les liens Première / Précédente / Suivante / Dernière
    // Elle renvoie uniquement les liens, jamais l'en-tête ni le pied de page
    private function lienPagination($nbPages)
    {
        // En XHTML, le & d'une URL doit être écrit &amp;
        $base = 'index.php?cmd=communes&amp;page=';

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