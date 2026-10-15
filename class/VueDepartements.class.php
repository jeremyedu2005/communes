<?php
// Fichier : class/VueDepartements.class.php
// Vue des départements :
//  - sans code de département : la liste des départements avec leur nombre de communes
//  - avec un code (?cmd=departements&dep=93) : les communes de ce département
 
class VueDepartements
{
    private $modele;   // Le modèle reçu du contrôleur
    private $dep;      // Code du département demandé ("" = liste des départements)
 
    public function __construct(Communes $modele, $dep = '')
    {
        $this->modele = $modele;
        $this->dep = trim($dep);
    }
 
    public function __toString()
    {
        // Selon la demande, on fabrique l'un ou l'autre contenu
        if ($this->dep === '') {
            $contenu = $this->listeDepartements();
            $titre   = 'Départements - Les communes de France';
        } else {
            $contenu = $this->communesDuDepartement();
            $titre   = 'Département ' . $this->dep . ' - Les communes de France';
        }
 
        // La page = début commun + contenu de cette vue + fin commune
        $debut = new VueDebutHTML($titre, ['communes.css' , 'departements.css']);
        $fin   = new VueFinHTML();
 
        return $debut . $contenu . $fin;
    }
 
    // Contenu 1 : tous les départements avec leur nombre de communes
    private function listeDepartements()
    {
        // Questions posées au modèle
        $departements = $this->modele->getNombreParDepartement();
        $min          = $this->modele->getDepartementMinCommunes();
 
        $h  = '        <section class="liste">' . "\n";
        $h .= '            <h2>Les départements</h2>' . "\n";
        $h .= '            <p>' . count($departements) . ' départements. Le moins fourni : '
            . htmlspecialchars($min['nom']) . ' (' . $min['nombre'] . ' commune(s)).</p>' . "\n";
 
        $h .= '            <div class="defilement">' . "\n";
        $h .= '            <table class="tableau">' . "\n";
        $h .= '                <thead>' . "\n";
        $h .= '                    <tr><th>Code</th><th>Département</th><th>Nombre de communes</th><th>Communes</th></tr>' . "\n";
        $h .= '                </thead>' . "\n";
        $h .= '                <tbody>' . "\n";
 
        foreach ($departements as $d) {
            // Code sur 2 caractères pour l'affichage : "1" devient "01", "2A" reste "2A"
            $code = str_pad($d['code'], 2, '0', STR_PAD_LEFT);
 
            // Lien vers la liste des communes de ce département (& s'écrit &amp; en XHTML)
            $lien = 'index.php?cmd=departements&amp;dep=' . urlencode($d['code']);
 
            $h .= '                    <tr>'
                . '<td>' . $code . '</td>'
                . '<td>' . htmlspecialchars($d['nom']) . '</td>'
                . '<td>' . number_format($d['nombre'], 0, ',', ' ') . '</td>'
                . '<td><a href="' . $lien . '">Voir les communes</a></td>'
                . '</tr>' . "\n";
        }
 
        $h .= '                </tbody>' . "\n";
        $h .= '            </table>' . "\n";
        $h .= '            </div>' . "\n";
        $h .= '        </section>' . "\n";
 
        return $h;
    }
 
    // Contenu 2 : les communes d'un seul département
    private function communesDuDepartement()
    {
        $communes = $this->modele->getListeCommuneDepartement($this->dep);
 
        $h  = '        <section class="liste">' . "\n";
        $h .= '            <p><a href="index.php?cmd=departements">← Retour à la liste des départements</a></p>' . "\n";
 
        // Code inconnu (ou saisi à la main dans l'URL) : message au lieu d'une page vide
        if (count($communes) === 0) {
            $h .= '            <p>Aucun département ne correspond à « ' . htmlspecialchars($this->dep) . ' ».</p>' . "\n";
            $h .= '        </section>' . "\n";
            return $h;
        }
 
        // Le nom du département se lit sur la première commune de la liste
        $code = str_pad($communes[0]['dep_code'], 2, '0', STR_PAD_LEFT);
        $nom  = $communes[0]['dep_nom'];
 
        $h .= '            <h2>Département ' . $code . ' - ' . htmlspecialchars($nom) . '</h2>' . "\n";
        $h .= '            <p>' . number_format(count($communes), 0, ',', ' ') . ' commune(s)</p>' . "\n";
 
        // Liste des communes, répartie en plusieurs colonnes par le CSS
        $h .= '            <ul class="colonnes">' . "\n";
        foreach ($communes as $c) {
            $h .= '                <li>' . htmlspecialchars($c['nom']) . '</li>' . "\n";
        }
        $h .= '            </ul>' . "\n";
        $h .= '        </section>' . "\n";
 
        return $h;
    }
}
