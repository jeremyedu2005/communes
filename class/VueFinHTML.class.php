<?php
// Fichier : class/VueFinHTML.class.php
// Fin commune à toutes les pages : fermeture de <main>, pied de page, fermeture du document

class VueFinHTML
{
    public function __toString()
    {
        ob_start();
        ?>
        </main>
        <footer class="pied">
            <p>Données : data.gouv.fr</p>
            <p class="mentions"> &#169;Droits d'auteurs 2026-RAODSON Miaro Jérémy-Tous droits réservés-<a class="legales"
                    href="index.php?cmd=mentions">Mentions légales</a></p>
        </footer>
        </div>
        </body>

        </html>
        <?php
        return ob_get_clean();
    }
}