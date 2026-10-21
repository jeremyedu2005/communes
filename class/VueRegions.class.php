<?php
// Fichier : class/VueRegions.class.php
// Vue des régions :
//  - sans nom de région : la liste des régions avec leur nombre de communes
//  - avec un nom (?cmd=regions&region=Bretagne) : les départements de la région et leurs communes

class VueRegions
{
    private $modele;    // Le modèle reçu du contrôleur
    private $region;    // Nom de la région demandée ("" = liste des régions)

    public function __construct(Communes $modele, $region = '')
    {
        $this->modele = $modele;
        $this->region = trim($region);
    }

    public function __toString()
    {
        // Selon la demande, on fabrique l'un ou l'autre contenu
        if ($this->region === '') {
            $contenu = $this->listeRegions();
            $titre   = 'Régions - Les communes de France';
        } else {
            $contenu = $this->departementsDeLaRegion();
            $titre   = 'Région ' . $this->region . ' - Les communes de France';
        }

        // La page = début commun + contenu de cette vue + fin commune
        $debut = new VueDebutHTML($titre, ['communes.css', 'departements.css','region.css']);
        $fin   = new VueFinHTML();

        return $debut . $contenu . $fin;
    }

    // Contenu 1 : toutes les régions avec leur nombre de communes
    private function listeRegions()
    {
        // Questions posées au modèle
        $regions = $this->modele->getNombreParRegion();
        $max     = $this->modele->getRegionMaxCommunes();

        $h  = '        <section class="liste">' . "\n";
        $h .= '            <h2>Les régions</h2>' . "\n";
        $h .= '            <p>' . count($regions) . ' régions. La plus fournie : '
            . htmlspecialchars($max['nom']) . ' (' . number_format($max['nombre'], 0, ',', ' ') . ' communes).</p>' . "\n";

        $h .= '            <div class="defilement">' . "\n";
        $h .= '            <table class="tableau">' . "\n";
        $h .= '                <thead>' . "\n";
        $h .= '                    <tr><th>Région</th><th>Nombre de communes</th><th>Départements et communes</th></tr>' . "\n";
        $h .= '                </thead>' . "\n";
        $h .= '                <tbody>' . "\n";

        foreach ($regions as $r) {
            // Lien vers le détail de cette région (urlencode protège les espaces et les accents ;
            // en XHTML, le & s'écrit &amp;)
            $lien = 'index.php?cmd=regions&amp;region=' . urlencode($r['nom']);

            $h .= '                    <tr>'
                . '<td>' . htmlspecialchars($r['nom']) . '</td>'
                . '<td>' . number_format($r['nombre'], 0, ',', ' ') . '</td>'
                . '<td><a href="' . $lien . '">Voir le détail</a></td>'
                . '</tr>' . "\n";
        }

        $h .= '                </tbody>' . "\n";
        $h .= '            </table>' . "\n";
        $h .= '            </div>' . "\n";
        $h .= '        </section>' . "\n";

        return $h;
    }

    // Contenu 2 : les départements d'une région, chacun avec ses communes
    private function departementsDeLaRegion()
    {
        $departements = $this->modele->getDepartementsEtCommunesRegion($this->region);

        $h  = '        <section class="liste">' . "\n";
        $h .= '            <p><a href="index.php?cmd=regions">← Retour à la liste des régions</a></p>' . "\n";

        // Nom inconnu (ou saisi à la main dans l'URL) : message au lieu d'une page vide
        if (count($departements) === 0) {
            $h .= '            <p>Aucune région ne correspond à « ' . htmlspecialchars($this->region) . ' ».</p>' . "\n";
            $h .= '        </section>' . "\n";
            return $h;
        }

        // Nombre total de communes de la région : somme des communes de chaque département
        $total = 0;
        foreach ($departements as $d) {
            $total += count($d['communes']);
        }

        $h .= '            <h2>' . htmlspecialchars($this->region) . '</h2>' . "\n";
        $h .= '            <p>' . count($departements) . ' département(s) - '
            . number_format($total, 0, ',', ' ') . ' commune(s)</p>' . "\n";

        // Un bloc pliable par département : un clic sur le titre déplie ses communes
        foreach ($departements as $d) {
            // Code sur 2 caractères pour l'affichage : "1" devient "01", "2A" reste "2A"
            $code = str_pad($d['code'], 2, '0', STR_PAD_LEFT);

            $h .= '            <details>' . "\n";
            $h .= '                <summary>Département ' . $code . ' - ' . htmlspecialchars($d['nom'])
                . ' (' . number_format(count($d['communes']), 0, ',', ' ') . ' communes)</summary>' . "\n";
            $h .= '                <ul class="colonnes">' . "\n";

            foreach ($d['communes'] as $nom) {
                $h .= '                    <li>' . htmlspecialchars($nom) . '</li>' . "\n";
            }

            $h .= '                </ul>' . "\n";
            $h .= '            </details>' . "\n";
        }

        $h .= '        </section>' . "\n";

        return $h;
    }
}