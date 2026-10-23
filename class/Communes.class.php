<?php
// Fichier : class/Communes.class.php
// Modèle : c'est la seule classe qui connaît la base de données.
// Elle interroge MySQL avec PDO et fournit des accesseurs aux vues.
// Elle n'affiche rien : l'affichage est le rôle des vues.

class Communes
{
    // Paramètres de connexion, sur le modèle de PDO_traitement.class.php.
    // Valeurs par défaut de XAMPP en local. Sur un hébergeur, il faudra les remplacer par celles
    // fournies par l'hébergeur (sans jamais publier de vrais identifiants sur GitHub).
    const HOST = "localhost";   // Nom de l'hôte
    const DB   = "communes";    // Nom de la base de données
    const USER = "root";        // Nom d'utilisateur
    const PASS = "";            // Mot de passe (vide sur XAMPP)
    const DSN  = "mysql:host=" . self::HOST . ";dbname=" . self::DB . ";charset=utf8mb4"; // DSN avec encodage

    protected $cnxDB;   // Instance de la connexion PDO

    // Constructeur : ouvre la connexion à la base
    public function __construct()
    {
        try {
            $this->cnxDB = new PDO(self::DSN, self::USER, self::PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // erreurs SQL = exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // résultats en tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES   => false,                   // vraies requêtes préparées
            ]);
        } catch (PDOException $e) {
            // Message volontairement vague : il ne doit pas révéler l'hôte ni l'utilisateur de la connexion
            die("Erreur : connexion à la base de données impossible. "
              . "Vérifie que MySQL est démarré et que la base a été créée (php import_csv.php).");
        }
    }

    // Méthode magique : appelée quand on fait echo $objet
    public function __toString()
    {
        return 'Communes instance (' . $this->getNombreTotal() . ' communes)';
    }

    // ---------- Outils internes ----------

    // Prépare une requête SQL, l'exécute avec ses paramètres et retourne le résultat
    private function requete($sql, $parametres = [])
    {
        $req = $this->cnxDB->prepare($sql);
        $req->execute($parametres);
        return $req;
    }

    // Met un texte sous une forme comparable : minuscules, sans accents,
    // tirets et apostrophes remplacés par des espaces ("Saint-Étienne" devient "saint etienne").
    // Elle est publique et statique : le script d'import s'en sert aussi, pour que le texte stocké
    // dans la base et le texte recherché soient transformés exactement de la même façon.
    public static function simplifier($texte)
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

    // Normalise un code de département : "01" et "1" deviennent "1", "2a" devient "2A"
    // (dans la base, les codes sont écrits sans zéro initial, comme dans le CSV)
    private function normaliserDep($code)
    {
        return strtoupper(ltrim((string)$code, '0'));
    }

    // Fabrique le motif d'une recherche LIKE : "saint etienne" devient "%saint etienne%".
    // Les caractères % et _ tapés par l'utilisateur sont neutralisés (sinon ils agiraient comme des jokers).
    private function motifRecherche($recherche)
    {
        return '%' . addcslashes($recherche, '%_\\') . '%';
    }

    // ---------- Listes de communes ----------

    // Retourne toutes les communes
    public function getListeCommunes()
    {
        return $this->requete(
            'SELECT c.nom, c.dep_code, d.dep_nom, r.reg_nom
             FROM communes c
             JOIN departements d ON d.dep_code = c.dep_code
             JOIN regions r ON r.reg_code = d.reg_code
             ORDER BY c.id'
        )->fetchAll();
    }

    // Retourne le nombre de communes dont le nom contient le texte recherché
    // Sans recherche (texte vide), retourne le nombre total de communes
    public function getNombreCommunesRecherche($recherche)
    {
        $recherche = self::simplifier($recherche);

        if ($recherche === '') {
            return $this->getNombreTotal();
        }

        return (int)$this->requete(
            'SELECT COUNT(*) FROM communes WHERE nom_simple LIKE :motif',
            ['motif' => $this->motifRecherche($recherche)]
        )->fetchColumn();
    }

    // Retourne une tranche de la liste (pour la pagination), en tenant compte de la recherche
    // $page commence à 1 ; $parPage = nombre de communes par page
    // Seules les lignes de la page demandée sont lues dans la base (LIMIT / OFFSET)
    public function getListeCommunesPage($page, $parPage, $recherche = '')
    {
        $parPage  = max(1, (int)$parPage);
        $decalage = (max(1, (int)$page) - 1) * $parPage;
        $recherche = self::simplifier($recherche);

        $sql = 'SELECT c.nom, c.dep_code, d.dep_nom, r.reg_nom
                FROM communes c
                JOIN departements d ON d.dep_code = c.dep_code
                JOIN regions r ON r.reg_code = d.reg_code';

        if ($recherche !== '') {
            $sql .= ' WHERE c.nom_simple LIKE :motif';
        }

        $sql .= ' ORDER BY c.id LIMIT :limite OFFSET :decalage';

        $req = $this->cnxDB->prepare($sql);
        if ($recherche !== '') {
            $req->bindValue(':motif', $this->motifRecherche($recherche));
        }
        // LIMIT et OFFSET doivent recevoir des entiers
        $req->bindValue(':limite', $parPage, PDO::PARAM_INT);
        $req->bindValue(':decalage', $decalage, PDO::PARAM_INT);
        $req->execute();

        return $req->fetchAll();
    }

    // Retourne le nombre de pages (au moins 1, même sans résultat)
    public function getNombrePages($parPage, $recherche = '')
    {
        $nombre = $this->getNombreCommunesRecherche($recherche);

        // ceil arrondit au nombre entier supérieur (348,68 devient 349)
        return max(1, (int)ceil($nombre / max(1, (int)$parPage)));
    }

    // Retourne les communes d'un département, par exemple getListeCommuneDepartement("93")
    public function getListeCommuneDepartement($dep)
    {
        return $this->requete(
            'SELECT c.nom, c.dep_code, d.dep_nom
             FROM communes c
             JOIN departements d ON d.dep_code = c.dep_code
             WHERE c.dep_code = :dep
             ORDER BY c.id',
            ['dep' => $this->normaliserDep($dep)]
        )->fetchAll();
    }

    // Retourne les communes d'une région, par exemple getListeCommuneRegion("Bretagne")
    public function getListeCommuneRegion($region)
    {
        return $this->requete(
            'SELECT c.nom, c.dep_code, d.dep_nom, r.reg_nom
             FROM communes c
             JOIN departements d ON d.dep_code = c.dep_code
             JOIN regions r ON r.reg_code = d.reg_code
             WHERE r.reg_nom = :region
             ORDER BY c.id',
            ['region' => $region]
        )->fetchAll();
    }

    // ---------- Départements et régions ----------

    // Retourne chaque département avec son nombre de communes :
    // [['code' => '93', 'nom' => 'Seine-Saint-Denis', 'nombre' => 39], ...]
    // Le comptage est fait par MySQL (COUNT + GROUP BY), pas par une boucle PHP.
    public function getNombreParDepartement()
    {
        return $this->requete(
            "SELECT d.dep_code AS code, d.dep_nom AS nom, COUNT(c.id) AS nombre
             FROM departements d
             JOIN communes c ON c.dep_code = d.dep_code
             GROUP BY d.dep_code, d.dep_nom
             ORDER BY LPAD(d.dep_code, 3, '0')"
        )->fetchAll();
    }

    // Retourne chaque région avec son nombre de communes :
    // [['nom' => 'Bretagne', 'nombre' => 1202], ...]
    public function getNombreParRegion()
    {
        return $this->requete(
            'SELECT r.reg_nom AS nom, COUNT(c.id) AS nombre
             FROM regions r
             JOIN departements d ON d.reg_code = r.reg_code
             JOIN communes c ON c.dep_code = d.dep_code
             GROUP BY r.reg_code, r.reg_nom
             ORDER BY r.reg_nom'
        )->fetchAll();
    }

    // Retourne la liste des départements (code et nom), sans les comptages
    public function getListeDepartements()
    {
        return $this->requete(
            "SELECT dep_code AS code, dep_nom AS nom
             FROM departements
             ORDER BY LPAD(dep_code, 3, '0')"
        )->fetchAll();
    }

    // Retourne la liste des noms de régions
    public function getListeRegions()
    {
        return $this->requete('SELECT reg_nom FROM regions ORDER BY reg_nom')->fetchAll(PDO::FETCH_COLUMN);
    }

    // Retourne les départements d'une région (code et nom)
    public function getListeDepartementsRegion($region)
    {
        return $this->requete(
            "SELECT d.dep_code AS code, d.dep_nom AS nom
             FROM departements d
             JOIN regions r ON r.reg_code = d.reg_code
             WHERE r.reg_nom = :region
             ORDER BY LPAD(d.dep_code, 3, '0')",
            ['region' => $region]
        )->fetchAll();
    }

    // Retourne les départements d'une région, chacun avec la liste de ses communes :
    // [['code' => '67', 'nom' => 'Bas-Rhin', 'communes' => ['Strasbourg', ...]], ...]
    public function getDepartementsEtCommunesRegion($region)
    {
        $req = $this->requete(
            "SELECT d.dep_code, d.dep_nom, c.nom
             FROM communes c
             JOIN departements d ON d.dep_code = c.dep_code
             JOIN regions r ON r.reg_code = d.reg_code
             WHERE r.reg_nom = :region
             ORDER BY LPAD(d.dep_code, 3, '0'), c.id",
            ['region' => $region]
        );

        // La requête renvoie une ligne par commune : on les regroupe par département
        $departements = [];
        foreach ($req as $ligne) {
            // Le préfixe 'd' évite que PHP transforme la clé "67" en entier
            $cle = 'd' . $ligne['dep_code'];

            if (!isset($departements[$cle])) {
                $departements[$cle] = ['code' => $ligne['dep_code'], 'nom' => $ligne['dep_nom'], 'communes' => []];
            }
            $departements[$cle]['communes'][] = $ligne['nom'];
        }
        return array_values($departements);
    }

    // ---------- Statistiques ----------

    // Retourne le nombre total de communes en France
    public function getNombreTotal()
    {
        return (int)$this->requete('SELECT COUNT(*) FROM communes')->fetchColumn();
    }

    // Retourne le nombre de communes d'un département
    public function getNombreCommunesDepartement($dep)
    {
        return (int)$this->requete(
            'SELECT COUNT(*) FROM communes WHERE dep_code = :dep',
            ['dep' => $this->normaliserDep($dep)]
        )->fetchColumn();
    }

    // Retourne le nombre de communes d'une région
    public function getNombreCommunesRegion($region)
    {
        return (int)$this->requete(
            'SELECT COUNT(*)
             FROM communes c
             JOIN departements d ON d.dep_code = c.dep_code
             JOIN regions r ON r.reg_code = d.reg_code
             WHERE r.reg_nom = :region',
            ['region' => $region]
        )->fetchColumn();
    }

    // Retourne la région qui a le plus de communes : ['nom' => ..., 'nombre' => ...]
    public function getRegionMaxCommunes()
    {
        $resultat = $this->requete(
            'SELECT r.reg_nom AS nom, COUNT(c.id) AS nombre
             FROM regions r
             JOIN departements d ON d.reg_code = r.reg_code
             JOIN communes c ON c.dep_code = d.dep_code
             GROUP BY r.reg_code, r.reg_nom
             ORDER BY nombre DESC, r.reg_nom
             LIMIT 1'
        )->fetch();

        return $resultat === false ? null : $resultat;
    }

    // Retourne le département qui a le moins de communes : ['code' => ..., 'nom' => ..., 'nombre' => ...]
    public function getDepartementMinCommunes()
    {
        $resultat = $this->requete(
            'SELECT d.dep_code AS code, d.dep_nom AS nom, COUNT(c.id) AS nombre
             FROM departements d
             JOIN communes c ON c.dep_code = d.dep_code
             GROUP BY d.dep_code, d.dep_nom
             ORDER BY nombre ASC, d.dep_code
             LIMIT 1'
        )->fetch();

        return $resultat === false ? null : $resultat;
    }
}