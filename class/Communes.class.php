<?php
// Fichier : class/Communes.class.php
// Modèle : c'est la seule classe qui connaît le fichier CSV.
// Elle le charge dans un tableau et fournit des accesseurs pour l'interroger.
// Elle n'affiche rien : l'affichage est le rôle des vues.

class Communes
{
    // Contient toutes les communes. Chaque commune est un tableau associatif :
    // ['nom', 'dep_code', 'dep_nom', 'reg_code', 'reg_nom']
    private $data = [];

    // Constructeur : exécuté automatiquement à la création de l'objet
    public function __construct()
    {
        // Chemin vers le CSV, calculé à partir de l'emplacement de ce fichier
        $chemin = 'data/communes.csv';

        // Vérifie si le fichier existe vraiment
        if (!file_exists($chemin)) {
            die("Erreur : le fichier des communes est introuvable : " . $chemin);
        }

        // Ouvre le fichier en lecture
        $fichier = fopen($chemin, 'r');
        if ($fichier === false) {
            die("Erreur : impossible d'ouvrir le fichier : " . $chemin);
        }

        // Lit la première ligne : les noms des colonnes (séparateur ';')
        $entetes = fgetcsv($fichier, 0, ';', '"', '\\');
        if ($entetes === false) {
            die("Erreur : le fichier des communes est vide");
        }

        // Supprime un éventuel BOM (caractère invisible) collé au premier nom de colonne
        $entetes[0] = str_replace("\xEF\xBB\xBF", '', $entetes[0]);

        // Vérifie que les colonnes dont on a besoin sont bien présentes
        $requises = ['nom_standard', 'dep_code', 'dep_nom', 'reg_code', 'reg_nom'];
        foreach ($requises as $colonne) {
            if (!in_array($colonne, $entetes)) {
                die("Erreur : colonne manquante dans le CSV : " . $colonne);
            }
        }

        // Lit les lignes une par une jusqu'à la fin du fichier
        while (($ligne = fgetcsv($fichier, 0, ';', '"', '\\')) !== false) {
            // Ignore les lignes vides ou incomplètes
            if (count($ligne) !== count($entetes)) {
                continue;
            }

            // Associe chaque valeur au nom de sa colonne
            $commune = array_combine($entetes, $ligne);

            // Ne garde que les colonnes utiles pour alléger la mémoire
            $this->data[] = [
                'nom'        => $commune['nom_standard'],
                'dep_code'   => $commune['dep_code'],
                'dep_nom'    => $commune['dep_nom'],
                'reg_code'   => $commune['reg_code'],
                'reg_nom'    => $commune['reg_nom'],
            ];
        }

        // Ferme le fichier
        fclose($fichier);
    }

    // Méthode magique : appelée quand on fait echo $objet
    public function __toString()
    {
        return 'Communes instance (' . count($this->data) . ' communes)';
    }

    // Normalise un code de département : "01" et "1" deviennent "1", "2A" reste "2A"
    // (dans le CSV, les codes sont écrits sans zéro initial)
    private function normaliserDep($code)
    {
        return strtoupper(ltrim((string)$code, '0'));
    }

    // ---------- Listes de communes ----------

    // Retourne toutes les communes
    public function getListeCommunes()
    {
        return $this->data;
    }

    /*    // Retourne une tranche de la liste des communes (pour la pagination)
    // $page commence à 1 ; $parPage = nombre de communes par page
    public function getListeCommunesPage($page, $parPage)
    {
        $page = max(1, (int)$page);

        // array_slice(tableau, position de départ, longueur)
        return array_slice($this->data, ($page - 1) * $parPage, $parPage);
    }

    // Retourne le nombre de pages nécessaires pour afficher toutes les communes
    public function getNombrePages($parPage)
    {
        // ceil arrondit au nombre entier supérieur (348,68 devient 349)
        return (int)ceil(count($this->data) / $parPage);
    }*/

    // Met un texte sous une forme comparable : minuscules, sans accents,
    // tirets et apostrophes remplacés par des espaces ("Saint-Étienne" devient "saint etienne")
    private function simplifier($texte)
    {
        $texte = mb_strtolower($texte, 'UTF-8');
        $texte = strtr($texte, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ÿ' => 'y', 'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae'
        ]);
        $texte = str_replace(['-', "'", '’'], ' ', $texte);

        return trim($texte);
    }

    // Retourne les communes dont le nom contient le texte recherché
    // Sans recherche (texte vide), retourne toutes les communes
    public function getListeCommunesRecherche($recherche)
    {
        $recherche = $this->simplifier($recherche);

        if ($recherche === '') {
            return $this->data;
        }

        // array_filter garde les communes dont le nom simplifié contient le texte
        return array_values(array_filter($this->data, function ($c) use ($recherche) {
            return strpos($this->simplifier($c['nom']), $recherche) !== false;
        }));
    }

    // Retourne le nombre de communes qui correspondent à la recherche
    public function getNombreCommunesRecherche($recherche)
    {
        return count($this->getListeCommunesRecherche($recherche));
    }

    // Retourne une tranche de la liste (pour la pagination), en tenant compte de la recherche
    // $page commence à 1 ; $parPage = nombre de communes par page
    public function getListeCommunesPage($page, $parPage, $recherche = '')
    {
        $page = max(1, (int)$page);
        $liste = $this->getListeCommunesRecherche($recherche);

        // array_slice(tableau, position de départ, longueur)
        return array_slice($liste, ($page - 1) * $parPage, $parPage);
    }

    // Retourne le nombre de pages (au moins 1, même sans résultat)
    public function getNombrePages($parPage, $recherche = '')
    {
        $nombre = count($this->getListeCommunesRecherche($recherche));

        // ceil arrondit au nombre entier supérieur (348,68 devient 349)
        return max(1, (int)ceil($nombre / $parPage));
    }    

    // Retourne les communes d'un département, par exemple getListeCommuneDepartement("93")
    public function getListeCommuneDepartement($dep)
    {
        $dep = $this->normaliserDep($dep);

        // array_filter garde les communes dont le département correspond,
        // array_values remet les clés à 0, 1, 2...
        return array_values(array_filter($this->data, function ($c) use ($dep) {
            return $this->normaliserDep($c['dep_code']) === $dep;
        }));
    }

    // Retourne les communes d'une région, par exemple getListeCommuneRegion("Bretagne")
    public function getListeCommuneRegion($region)
    {
        return array_values(array_filter($this->data, function ($c) use ($region) {
            return $c['reg_nom'] === $region;
        }));
    }

    // ---------- Départements et régions ----------

    // Retourne chaque département avec son nombre de communes :
    // [['code' => '93', 'nom' => 'Seine-Saint-Denis', 'nombre' => 39], ...]
    public function getNombreParDepartement()
    {
        $comptes = [];
        foreach ($this->data as $c) {
            // Le préfixe 'd' évite que PHP transforme la clé "93" en entier
            $cle = 'd' . $c['dep_code'];

            // Première fois qu'on voit ce département : on crée son entrée
            if (!isset($comptes[$cle])) {
                $comptes[$cle] = ['code' => $c['dep_code'], 'nom' => $c['dep_nom'], 'nombre' => 0];
            }
            $comptes[$cle]['nombre']++;
        }
        return array_values($comptes);
    }

    // Retourne chaque région avec son nombre de communes :
    // [['nom' => 'Bretagne', 'nombre' => 1202], ...]
    public function getNombreParRegion()
    {
        $comptes = [];
        foreach ($this->data as $c) {
            $cle = 'r' . $c['reg_nom'];

            if (!isset($comptes[$cle])) {
                $comptes[$cle] = ['nom' => $c['reg_nom'], 'nombre' => 0];
            }
            $comptes[$cle]['nombre']++;
        }
        return array_values($comptes);
    }

    // Retourne la liste des départements (code et nom), sans les comptages
    public function getListeDepartements()
    {
        return array_map(function ($d) {
            return ['code' => $d['code'], 'nom' => $d['nom']];
        }, $this->getNombreParDepartement());
    }

    // Retourne la liste des noms de régions
    public function getListeRegions()
    {
        return array_column($this->getNombreParRegion(), 'nom');
    }

    // Retourne les départements d'une région (code et nom)
    public function getListeDepartementsRegion($region)
    {
        $departements = [];
        foreach ($this->data as $c) {
            if ($c['reg_nom'] === $region) {
                // La clé évite les doublons : un département n'est ajouté qu'une fois
                $departements['d' . $c['dep_code']] = ['code' => $c['dep_code'], 'nom' => $c['dep_nom']];
            }
        }
        return array_values($departements);
    }

    // ---------- Statistiques ----------

    // Retourne le nombre total de communes en France
    public function getNombreTotal()
    {
        return count($this->data);
    }

    // Retourne le nombre de communes d'un département
    public function getNombreCommunesDepartement($dep)
    {
        return count($this->getListeCommuneDepartement($dep));
    }

    // Retourne le nombre de communes d'une région
    public function getNombreCommunesRegion($region)
    {
        return count($this->getListeCommuneRegion($region));
    }

    // Retourne la région qui a le plus de communes : ['nom' => ..., 'nombre' => ...]
    public function getRegionMaxCommunes()
    {
        $max = null;
        foreach ($this->getNombreParRegion() as $r) {
            if ($max === null || $r['nombre'] > $max['nombre']) {
                $max = $r;
            }
        }
        return $max;
    }

    // Retourne le département qui a le moins de communes : ['code' => ..., 'nom' => ..., 'nombre' => ...]
    public function getDepartementMinCommunes()
    {
        $min = null;
        foreach ($this->getNombreParDepartement() as $d) {
            if ($min === null || $d['nombre'] < $min['nombre']) {
                $min = $d;
            }
        }
        return $min;
    }
}