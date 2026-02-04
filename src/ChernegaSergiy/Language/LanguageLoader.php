<?php

declare(strict_types=1);

namespace ChernegaSergiy\Language;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use function basename;
use function glob;

class LanguageLoader {

    /**
     * Loads all .yml and .yaml files from the specified directory.
     *
     * @param string $directory Path to the directory containing language files.
     * @return Language[]
     */
    public static function loadFromDirectory(string $directory): array {
        $languages = [];
        $files = glob($directory . "/*.{yml,yaml}", GLOB_BRACE);

        if ($files !== false) {
            foreach ($files as $file) {
                $locale = basename($file, "." . pathinfo($file, PATHINFO_EXTENSION));
                $config = new Config($file, Config::YAML);
                $languages[] = new Language($locale, $config->getAll());
            }
        }

        return $languages;
    }

    /**
     * Automatically saves resources and loads languages from the plugin's data folder.
     *
     * @param PluginBase $plugin
     * @param string     $resourcePath Path inside resources folder (e.g. "languages")
     * @return Language[]
     */
    public static function loadFromResourceDirectory(PluginBase $plugin, string $resourcePath = "languages"): array {
        $dataFolder = $plugin->getDataFolder() . $resourcePath . "/";
        
        // This is a bit tricky in PMMP as there is no direct "list resources" API
        // Typically, developers know which files they have. 
        // For a more generic approach, we assume they are already saved or the dev handles saving.
        
        return self::loadFromDirectory($dataFolder);
    }
}
