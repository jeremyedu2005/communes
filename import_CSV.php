<?php
// Fichier : import_csv.php
// Convertit le CSV des communes en base de données MySQL.
// À lancer UNE SEULE FOIS (et à chaque fois que le CSV change), dans le terminal :
//     php import_csv.php

// Sécurité : ce script supprime et recrée les tables, il ne doit jamais être lancé depuis un navigateur
if (PHP_SAPI !== 'cli') {
    die("Ce script se lance uniquement dans le terminal : php import_csv.php\n");
}

// On réutilise les paramètres de connexion et la fonction simplifier() du modèle
require_once 'class/Communes.class.php';

$chemin = 'data/communes.csv';
if (!file_exists($chemin)) {
    die("Erreur : fichier introuvable : " . $chemin . "\n");
}

try {
    // 1. Connexion à la base, en utilisant les paramètres du modèle
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    try {
        // Cas d'un hébergeur : la base existe déjà (créée dans son interface), on s'y connecte directement
        $pdo = new PDO(Communes::DSN, Communes::USER, Communes::PASS, $options);
    } catch (PDOException $e) {
        // Cas de XAMPP en local : on se connecte au serveur sans base, puis on crée la base
        $pdo = new PDO('mysql:host=' . Communes::HOST . ';charset=utf8mb4', Communes::USER, Communes::PASS, $options);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . Communes::DB . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . Communes::DB . '`');
    }

    // 2. Création des tables (on supprime d'abord les anciennes, en commençant par celles qui dépendent des autres)
    $pdo->exec('DROP TABLE IF EXISTS communes');
    $pdo->exec('DROP TABLE IF EXISTS departements');
    $pdo->exec('DROP TABLE IF EXISTS regions');

    // Une région : son code est la clé primaire, son nom est unique
    $pdo->exec('CREATE TABLE regions (
        reg_code VARCHAR(3)   NOT NULL,
        reg_nom  VARCHAR(100) NOT NULL,
        PRIMARY KEY (reg_code),
        UNIQUE KEY uq_regions_nom (reg_nom)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    // Un département appartient à une région (clé étrangère + index sur reg_code)
    $pdo->exec('CREATE TABLE departements (
        dep_code VARCHAR(3)   NOT NULL,
        dep_nom  VARCHAR(100) NOT NULL,
        reg_code VARCHAR(3)   NOT NULL,
        PRIMARY KEY (dep_code),
        KEY idx_departements_region (reg_code),
        CONSTRAINT fk_departements_region FOREIGN KEY (reg_code) REFERENCES regions (reg_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    // Une commune appartient à un département (clé étrangère + index sur dep_code).
    // nom_simple (nom sans accents ni majuscules) est calculé une seule fois ici, pour la recherche.
    $pdo->exec('CREATE TABLE communes (
        id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        nom        VARCHAR(150) NOT NULL,
        nom_simple VARCHAR(150) NOT NULL,
        dep_code   VARCHAR(3)   NOT NULL,
        PRIMARY KEY (id),
        KEY idx_communes_dep (dep_code),
        KEY idx_communes_nom_simple (nom_simple),
        CONSTRAINT fk_communes_dep FOREIGN KEY (dep_code) REFERENCES departements (dep_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    // 3. Lecture du CSV (séparateur ';')
    $fichier = fopen($chemin, 'r');
    if ($fichier === false) {
        die("Erreur : impossible d'ouvrir " . $chemin . "\n");
    }

    $entetes = fgetcsv($fichier, 0, ';', '"', '\\');
    $entetes[0] = str_replace("\xEF\xBB\xBF", '', $entetes[0]);   // supprime un éventuel BOM

    foreach (['nom_standard', 'dep_code', 'dep_nom', 'reg_code', 'reg_nom'] as $colonne) {
        if (!in_array($colonne, $entetes)) {
            die("Erreur : colonne manquante dans le CSV : " . $colonne . "\n");
        }
    }

    $regions      = [];   // code => nom
    $departements = [];   // code => ['nom' => ..., 'reg' => code de la région]
    $communes     = [];   // liste de [nom, nom_simple, code du département]

    while (($ligne = fgetcsv($fichier, 0, ';', '"', '\\')) !== false) {
        if (count($ligne) !== count($entetes)) {
            continue;   // ligne vide ou incomplète
        }
        $l = array_combine($entetes, $ligne);

        // Une région ou un département apparaît dans le CSV sur de nombreuses lignes :
        // en les rangeant par code, chacun n'est gardé qu'une seule fois
        $regions[(string)$l['reg_code']] = $l['reg_nom'];
        $departements[(string)$l['dep_code']] = ['nom' => $l['dep_nom'], 'reg' => (string)$l['reg_code']];
        $communes[] = [$l['nom_standard'], Communes::simplifier($l['nom_standard']), (string)$l['dep_code']];
    }
    fclose($fichier);

    // 4. Insertion dans une transaction : tout est enregistré ou rien (et c'est beaucoup plus rapide)
    $pdo->beginTransaction();

    $req = $pdo->prepare('INSERT INTO regions (reg_code, reg_nom) VALUES (:code, :nom)');
    foreach ($regions as $code => $nom) {
        $req->execute(['code' => (string)$code, 'nom' => $nom]);
    }

    $req = $pdo->prepare('INSERT INTO departements (dep_code, dep_nom, reg_code) VALUES (:code, :nom, :reg)');
    foreach ($departements as $code => $d) {
        $req->execute(['code' => (string)$code, 'nom' => $d['nom'], 'reg' => $d['reg']]);
    }

    $req = $pdo->prepare('INSERT INTO communes (nom, nom_simple, dep_code) VALUES (:nom, :simple, :dep)');
    foreach ($communes as $c) {
        $req->execute(['nom' => $c[0], 'simple' => $c[1], 'dep' => $c[2]]);
    }

    $pdo->commit();

    // 5. Bilan : à comparer avec les valeurs attendues (34868 communes, 101 départements, 18 régions)
    echo "Import terminé.\n";
    echo " - communes     : " . $pdo->query('SELECT COUNT(*) FROM communes')->fetchColumn() . "\n";
    echo " - départements : " . $pdo->query('SELECT COUNT(*) FROM departements')->fetchColumn() . "\n";
    echo " - régions      : " . $pdo->query('SELECT COUNT(*) FROM regions')->fetchColumn() . "\n";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Erreur pendant l'import : " . $e->getMessage() . "\n");
}