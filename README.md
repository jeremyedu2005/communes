## Les communes de France
 
[Demo](https://raodson-jeremy.com/)
 
**Les communes de France** est une application web qui permet de consulter les communes françaises, leurs départements et leurs régions, à partir des données ouvertes publiées sur data.gouv.fr. Elle affiche des statistiques (nombre de communes en France, par département et par région) et propose une recherche par nom de commune.
 
Projet réalisé dans le cadre de la formation BUT MMI (Métiers du Multimédia et de l'Internet) à l'IUT de Bobigny.
 
 
## Présentation du projet
 
### Objectif
 
Extraire les données du fichier des communes de France, les regrouper par département puis par région, et les présenter dans des pages XHTML5 lisibles, avec des statistiques.
 
### Fonctionnalités
 
- **Accueil** : nombre total de communes en France, région qui compte le plus de communes, département qui en compte le moins.
- **Toutes les communes** : liste paginée (100 communes par page) avec une barre de recherche par nom, qui ignore les accents, les majuscules et les tirets.
- **Départements** : liste des départements avec leur nombre de communes, puis la liste des communes d'un département. Un champ permet aussi de saisir directement un code de département (par exemple 93).
- **Régions** : liste des régions avec leur nombre de communes, puis, pour une région choisie, ses départements et leurs communes (un bloc pliable par département).
- **Mentions légales**.
- **Affichage adapté** aux écrans d'ordinateur, de tablette et de téléphone.
### Source des données
 
Les données proviennent du jeu de données [Liste des communes de France](https://www.data.gouv.fr/datasets/liste-des-communes-de-france-code-insee-codes-postaux-epci-population-superficie-62-indicateurs), publié sur data.gouv.fr. La licence de réutilisation est indiquée sur la page de ce jeu de données.
 
 
## Technologies et méthodologies
 
### Technologies
 
- **PHP** en programmation orientée objet, sans framework
- **XHTML5**, servi avec le type `application/xhtml+xml` (la page est donc lue en mode XML strict)
- **CSS** : Flexbox, media queries et `clamp()` pour l'adaptation aux différents écrans
- **CSV** comme source de données, lu avec `fgetcsv`
- **XAMPP** pour le serveur local, **VS Code** comme éditeur
- **Git** et **GitHub** pour le versionnement
### Méthodologies
 
- **Architecture MVC** :
  - le **modèle** (`Communes`) est la seule classe qui connaît le fichier CSV : elle le lit, le stocke dans un tableau et fournit des accesseurs pour l'interroger ;
  - les **vues** (`Vue*`) interrogent le modèle et fabriquent le HTML, sans jamais lire le fichier ;
  - le **contrôleur** (`index.php`) lit l'URL (`?cmd=...`), crée le modèle, crée la vue et lui transmet le modèle.
- **Autoloader** (`inc/poo.inc.php`) : les classes sont chargées automatiquement, avec un seul `require_once` dans le contrôleur.
- **Traitement des données** avec les fonctions de tableaux de PHP (`array_filter`, `array_slice`, `array_column`...).
- **Versionnement** : une branche par fonctionnalité (`Accueil`, `communes`, `departements`...), fusionnées dans la branche principale.
### Génération du code par intelligence artificielle
 
**L'intégralité du code de ce projet (PHP, HTML et CSS) a été générée par une intelligence artificielle** (Claude, développé par Anthropic). Mon rôle a consisté à définir les besoins à partir de l'énoncé du sujet, à relire et comprendre le code produit, à l'adapter à mon projet (organisation des fichiers, noms, chemins, feuilles de style), à le tester dans le navigateur, à signaler les erreurs rencontrées pour qu'elles soient corrigées, et à versionner le projet avec Git.
 
 
## Arborescence du projet
 
```
communes/
├── index.php                      Contrôleur : lit l'URL et choisit la vue
├── inc/
│   └── poo.inc.php                Autoloader des classes
├── data/
│   └── communes.csv               Données des communes (data.gouv.fr)
├── class/
│   ├── Communes.class.php         Modèle : lecture du CSV, regroupements, statistiques
│   ├── VueDebutHTML.class.php     Début commun des pages (en-tête, menu)
│   ├── VueFinHTML.class.php       Fin commune des pages (pied de page)
│   ├── VueAccueil.class.php       Page d'accueil et statistiques
│   ├── VueCommunes.class.php      Liste des communes, recherche et pagination
│   ├── VueDepartements.class.php  Départements et leurs communes
│   ├── VueRegions.class.php       Régions, départements et communes
│   └── VueMentionlegales.class.php  Mentions légales
└── css/
    ├── menu.css                   Style commun (en-tête, menu, pied de page)
    ├── accueil.css                Cartes de la page d'accueil
    ├── communes.css               Tableaux, recherche et pagination
    ├── departements.css           Liste des communes d'un département
    ├── region.css                 Blocs pliables des régions
    └── mentions.css               Mentions légales
```