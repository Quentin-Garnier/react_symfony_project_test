<?php

namespace App\Entity;

class DataCampagneUpdater
{
    /**
     * Met à jour la variable $campagnes dans un fichier externe
     *
     * @param string $filePath Chemin du fichier cible
     * @param array $newCampagne Nouvelle campagne à ajouter
     * @return void
     */
    public static function updateCampagnesVariable(string $filePath, array $campagnes): void
    {
        // Lire le contenu du fichier cible
        $content = file_get_contents($filePath);

        // Utiliser une regex pour trouver et extraire le tableau $campagnes
        if (preg_match('/private static array \$campagnes = (.*?);/s', $content, $matches)) {
            // Exporter le tableau mis à jour en PHP valide
            $updatedArrayCode = var_export($campagnes, true);

            // Remplacer la variable $campagnes dans le contenu du fichier
            $updatedContent = preg_replace(
                '/private static array \$campagnes = .*?;/s',
                "private static array \$campagnes = {$updatedArrayCode};",
                $content
            );

            // Réécrire le contenu modifié dans le fichier
            file_put_contents($filePath, $updatedContent);
        } else {
            throw new \Exception("La variable \$campagnes n'a pas été trouvée dans le fichier.");
        }
    }

    /**
     * Met à jour la variable $forms dans un fichier externe
     *
     * @param string $filePath Chemin du fichier cible
     * @param array $newForm Nouveau formulaire à ajouter
     * @return void
     */
    public static function updateFormsVariable(string $filePath, array $forms): void
    {
        // Lire le contenu du fichier cible
        $content = file_get_contents($filePath);

        // Utiliser une regex pour trouver et extraire le tableau $forms
        if (preg_match('/private static array \$forms = (.*?);/s', $content, $matches)) {
            // Exporter le tableau mis à jour en PHP valide
            $updatedArrayCode = var_export($forms, true);

            // Remplacer la variable $forms dans le contenu du fichier
            $updatedContent = preg_replace(
                '/private static array \$forms = .*?;/s',
                "private static array \$forms = {$updatedArrayCode};",
                $content
            );

            // Réécrire le contenu modifié dans le fichier
            file_put_contents($filePath, $updatedContent);
        } else {
            throw new \Exception("La variable \$forms n'a pas été trouvée dans le fichier.");
        }
    }
}
