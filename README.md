## Les communes de France
 
[Demo](https://raodson-jeremy.com/Projets/communes/index.php)
 
**Les communes de France** est une application web qui permet de consulter les communes françaises, leurs départements et leurs régions, à partir des données ouvertes publiées sur data.gouv.fr. Elle affiche des statistiques (nombre de communes en France, par département et par région) et propose une recherche par nom de commune.
 
Projet réalisé dans le cadre de la formation BUT MMI (Métiers du Multimédia et de l'Internet) à l'IUT de Bobigny.
 
 
## Présentation du projet
 
### Objectif
 
Convertir le fichier des communes de France en base de données, interroger cette base avec PDO, puis présenter les résultats dans des pages XHTML5 lisibles, avec des statistiques.
 
### Fonctionnalités
 
- **Accueil** : nombre total de communes en France, région qui compte le plus de communes, département qui en compte le moins.
- **Toutes les communes** : liste paginée (100 communes par page) avec une barre de recherche par nom, qui ignore les accents, les majuscules et les tirets.
- **Départements** : liste des départements avec leur nombre de communes, puis la liste des communes d'un département. Un champ permet aussi de saisir directement un code de département (par exemple 93).
- **Régions** : liste des régions avec leur nombre de communes, puis, pour une région choisie, ses départements et leurs communes (un bloc pliable par département).
- **Mentions légales**.
- **Affichage adapté** aux écrans d'ordinateur, de tablette et de téléphone, avec un menu hamburger sous 1080 px.
### Source des données
 
Les données proviennent du jeu de données [Liste des communes de France](https://www.data.gouv.fr/datasets/liste-des-communes-de-france-code-insee-codes-postaux-epci-population-superficie-62-indicateurs), publié sur data.gouv.fr. La licence de réutilisation est indiquée sur la page de ce jeu de données.
 
 
## Technologies et méthodologies
 
### Technologies
 
- **PHP** en programmation orientée objet, sans framework
- **PDO** et **MySQL** pour la base de données (administration avec phpMyAdmin)
- **XHTML5**, servi avec le type `application/xhtml+xml` (la page est donc lue en mode XML strict)
- **CSS** : Flexbox, media queries et `clamp()` pour l'adaptation aux différents écrans, menu hamburger réalisé en CSS seul
- **CSV** comme source brute des données, lue une seule fois par le script d'import
- **XAMPP** pour le serveur local, **VS Code** comme éditeur
- **Git** et **GitHub** pour le versionnement
- **Aucun JavaScript** : tout est géré côté serveur, conformément à la consigne du projet
### Méthodologies
 
- **Architecture MVC** :
  - le **modèle** (`Communes`) est la seule classe qui connaît la base de données : elle ouvre la connexion PDO, exécute les requêtes et fournit des accesseurs ;
  - les **vues** (`Vue*`) interrogent le modèle et fabriquent le HTML, sans jamais accéder à la base ;
  - le **contrôleur** (`index.php`) lit l'URL (`?cmd=...`), crée le modèle, crée la vue et lui transmet le modèle.
- **Base de données optimisée pour les requêtes** :
  - trois tables liées (`regions`, `departements`, `communes`), pour ne stocker qu'une fois le nom de chaque région et de chaque département ;
  - clés primaires, clés étrangères et index sur les colonnes utilisées par les jointures et les recherches ;
  - comptages réalisés par MySQL (`COUNT` et `GROUP BY`) et pagination avec `LIMIT` et `OFFSET`, pour ne lire que les lignes affichées ;
  - requêtes préparées, qui protègent contre les injections SQL.
- **Import des données** : le script `import_csv.php`, lancé dans le terminal, crée la base, les tables et y insère le contenu du CSV dans une transaction.
- **Autoloader** (`inc/poo.inc.php`) : les classes sont chargées automatiquement, avec un seul `require_once` dans le contrôleur.
- **Versionnement** : une branche par fonctionnalité (`Accueil`, `communes`, `departements`...), fusionnées dans la branche principale.
### Génération du code par intelligence artificielle
 
**L'intégralité du code de ce projet (PHP, SQL, HTML et CSS) a été générée par une intelligence artificielle** (Claude, développé par Anthropic). Mon rôle a consisté à définir les besoins à partir de l'énoncé du sujet, à relire et comprendre le code produit, à l'adapter à mon projet (organisation des fichiers, noms, chemins, feuilles de style, exemple de classe PDO fourni par mon enseignant), à le tester dans le navigateur, à signaler les erreurs rencontrées pour qu'elles soient corrigées, et à versionner et déployer le projet.
 
 
## Arborescence du projet
 
```
communes/
├── index.php                      Contrôleur : lit l'URL et choisit la vue
├── import_csv.php                 Script d'import : CSV vers base MySQL (terminal uniquement)
├── .gitignore                     Fichiers ignorés par Git
├── inc/
│   └── poo.inc.php                Autoloader des classes
├── data/
│   └── communes.csv               Données brutes (data.gouv.fr), utilisées seulement par l'import
├── class/
│   ├── Communes.class.php         Modèle : connexion PDO, requêtes, statistiques
│   ├── VueDebutHTML.class.php     Début commun des pages (en-tête, menu hamburger)
│   ├── VueFinHTML.class.php       Fin commune des pages (pied de page)
│   ├── VueAccueil.class.php       Page d'accueil et statistiques
│   ├── VueCommunes.class.php      Liste des communes, recherche et pagination
│   ├── VueDepartements.class.php  Départements et leurs communes
│   ├── VueRegions.class.php       Régions, départements et communes
│   └── VueMention.class.php  Mentions légales
└── css/
    ├── menu.css                   Style commun (en-tête, menu, pied de page)
    ├── accueil.css                Cartes de la page d'accueil
    ├── communes.css               Tableaux, recherche et pagination
    ├── departements.css           Liste des communes d'un département
    ├── region.css                 Blocs pliables des régions
    └── mentions.css               Mentions légales
```
 
 
## Lancer le projet en local
 
1. Placer le dossier du projet dans `C:\xampp\htdocs\communes`.
2. Démarrer **Apache** et **MySQL** dans le panneau de contrôle de XAMPP.
3. Placer le fichier CSV des communes dans `data/communes.csv` (encodage UTF-8, séparateur `;`).
4. Dans un terminal, à la racine du projet, lancer l'import une seule fois : `php import_csv.php`. Il crée la base `communes` et affiche un bilan : 34868 communes, 101 départements, 18 régions.
5. Ouvrir `http://localhost/communes/`.
Les paramètres de connexion à la base sont les constantes `HOST`, `DB`, `USER` et `PASS` de `class/Communes.class.php` (valeurs par défaut de XAMPP). Ils sont à adapter pour un hébergement en ligne, sans jamais publier de vrais identifiants.
