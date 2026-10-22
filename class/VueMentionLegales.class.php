<?php
// Fichier : class/VueMentionlegales.class.php
// Vue des mentions légales : elle fabrique son contenu, puis l'entoure du début et de la fin communs

class VueMentionlegales
{
    public function __toString()
    {
        // Le contenu de la page est mis en mémoire tampon dans la variable $contenu
        ob_start();

        ?>
        <div class="mentions-legales">
            <div class="titre-mentions">
                <h2>Mentions légales</h2>
            </div>

            <div class="section-mentions proprietaire">
                <h3 class="editeur">Éditeur du site</h3>
                <p><strong>Propriétaire :</strong> RAODSON Miaro Jérémy</p>
                <p><strong>Statut :</strong> Étudiant en BUT MMI (Métiers du Multimédia et de l'Internet) à l'IUT de Bobigny</p>
                <p><strong>Adresse e-mail :</strong> jraodson@gmail.com</p>
            </div>

            <div class="section-mentions hebergeur">
                <h3>Hébergement du site</h3>
                <p><strong>Hébergeur :</strong> o2switch</p>
                <p><strong>Activité principale (NAF/APE) :</strong> Traitement de données, hébergement et activités connexes</p>
                <p><strong>Adresse :</strong> Chem. des Pardiaux, 63000 Clermont-Ferrand</p>
                <p><strong>Numéro de téléphone :</strong> 04 44 44 60 40</p>
                <p><strong>Lien hébergeur :</strong> <a href="https://www.o2switch.fr/" target="_blank" rel="noopener noreferrer">https://www.o2switch.fr/</a></p>
            </div>

            <div class="section-mentions propriete">
                <h3>Propriété intellectuelle</h3>
                <p>Le code et la mise en page de ce site sont l'œuvre de leur auteur, sauf mention contraire.</p>
                <p>Les données sur les communes sont issues de data.gouv.fr (voir « Source des données »).
                    Ce site est un projet académique sans but commercial.</p>
            </div>

            <div class="section-mentions activite">
                <h3>Activité</h3>
                <p><strong>Activité :</strong> Site de consultation des communes de France : liste des communes, des
                    départements et des régions, avec des statistiques et une recherche par nom de commune.</p>
                <p><strong>Activité professionnelle déclarée :</strong> Aucune</p>
                <p><strong>Nature du projet :</strong> Projet académique dans le cadre de la formation BUT MMI</p>
            </div>

            <div class="section-mentions cookies">
                <h3>Cookies et données personnelles</h3>
                <p>Le site n'utilise aucun cookie.</p>
                <p>Aucune donnée personnelle n'est collectée, stockée ou transmise à des tiers.</p>
                <p>Le texte saisi dans la barre de recherche sert uniquement à filtrer la liste des communes affichée :
                    il n'est pas enregistré.</p>
            </div>

            <div class="section-mentions description">
                <h3>Description du site</h3>
                <p>Ce site présente les communes de France à partir des données ouvertes publiées sur data.gouv.fr
                    (voir « Source des données »). Il a été réalisé à des fins pédagogiques dans le cadre du BUT MMI.</p>
                <p><strong>Fonctionnalités principales :</strong></p>
                <ul>
                    <li>Liste de toutes les communes, avec pagination</li>
                    <li>Recherche d'une commune par son nom, sans tenir compte des accents ni des majuscules</li>
                    <li>Liste des départements avec leur nombre de communes, puis les communes de chaque département</li>
                    <li>Liste des régions avec leur nombre de communes, leurs départements et leurs communes</li>
                    <li>Statistiques : total des communes, région la plus fournie, département le moins fourni</li>
                    <li>Affichage adapté aux écrans d'ordinateur, de tablette et de téléphone</li>
                </ul>
                <p>Ce site n'a aucun but commercial et est destiné uniquement à des fins éducatives.</p>
            </div>

            <div class="section-mentions source">
                <h3>Source des données</h3>
                <p>Les données sur les communes proviennent du jeu de données
                    <a href="https://www.data.gouv.fr/datasets/liste-des-communes-de-france-code-insee-codes-postaux-epci-population-superficie-62-indicateurs" target="_blank" rel="noopener noreferrer">« Liste des communes de France »</a>,
                    publié sur data.gouv.fr, la plateforme ouverte des données publiques françaises.</p>
                <p>Le site lit ce fichier pour afficher la liste des communes, des départements et des régions.</p>
            </div>
        </div>
        <?php
        // On récupère le contenu mis en mémoire tampon
        $contenu = ob_get_clean();

        // La page = début commun + contenu de cette vue + fin commune
        // (ces lignes doivent rester À L'INTÉRIEUR de __toString())
        $debut = new VueDebutHTML('Mentions légales - Les communes de France', ['mentions.css']);
        $fin   = new VueFinHTML();

        return $debut . $contenu . $fin;
    }
}