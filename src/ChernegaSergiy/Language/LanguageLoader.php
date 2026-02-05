<?php

declare(strict_types=1);

namespace ChernegaSergiy\Language;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use function basename;
use function file_get_contents;
use function glob;
use function json_decode;
use function pathinfo;
use function preg_match;

class LanguageLoader {

    /**
     * Loads all .yml, .yaml, and .json files from the specified directory that have valid locale names.
     *
     * @param string $directory Path to the directory containing language files.
     * @return Language[]
     */
    public static function loadFromDirectory(string $directory): array {
        $languages = [];
        $files = glob($directory . "/*.{yml,yaml,json}", GLOB_BRACE);

        if ($files !== false) {
            foreach ($files as $file) {
                $locale = basename($file, "." . pathinfo($file, PATHINFO_EXTENSION));
                
                if (!self::isValidLocale($locale)) {
                    continue;
                }
                
                try {
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    if ($extension === 'json') {
                        $data = json_decode(file_get_contents($file), true);
                        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                            continue;
                        }
                    } else {
                        $config = new Config($file, Config::YAML);
                        $data = $config->getAll();
                        if (!is_array($data)) {
                            continue;
                        }
                    }
                    
                    if (empty($data)) {
                        continue;
                    }
                    
                    $languages[] = new Language($locale, $data);
                } catch (\Exception $e) {
                    // Skip invalid files
                }
            }
        }

        return $languages;
    }

    /**
     * Checks if a string is a valid locale format (e.g., en_US, zh-CN).
     *
     * @param string $locale
     * @return bool
     */
    private static function isValidLocale(string $locale): bool {
        return preg_match('/^[a-z]{2,3}(_|-)[A-Z]{2,4}$/', $locale) === 1;
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
