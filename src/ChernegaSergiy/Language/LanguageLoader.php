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

class LanguageLoader {

    private static array $VALID_LOCALES = [
        "en_US" => "English (United States)",
        "en_GB" => "English (United Kingdom)",
        "de_DE" => "Deutsch (Deutschland)",
        "es_ES" => "Español (España)",
        "es_MX" => "Español (México)",
        "fr_FR" => "Français (France)",
        "fr_CA" => "Français (Canada)",
        "it_IT" => "Italiano (Italia)",
        "ja_JP" => "日本語 (日本)",
        "ko_KR" => "한국어 (대한민국)",
        "pt_BR" => "Português (Brasil)",
        "pt_PT" => "Português (Portugal)",
        "ru_RU" => "Русский (Россия)",
        "zh_CN" => "中文(简体)",
        "zh_TW" => "中文(繁體)",
        "nl_NL" => "Nederlands (Nederland)",
        "bg_BG" => "Български (България)",
        "cs_CZ" => "Čeština (Česko)",
        "da_DK" => "Dansk (Danmark)",
        "el_GR" => "Ελληνικά (Ελλάδα)",
        "fi_FI" => "Suomi (Suomi)",
        "hu_HU" => "Magyar (Magyarország)",
        "id_ID" => "Indonesia (Indonesia)",
        "nb_NO" => "Norsk bokmål (Norge)",
        "pl_PL" => "Polski (Polska)",
        "sk_SK" => "Slovenčina (Slovensko)",
        "sv_SE" => "Svenska (Sverige)",
        "tr_TR" => "Türkçe (Türkiye)",
        "uk_UA" => "Українська (Україна)"
    ];

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
     * Checks if a string is a valid locale from the predefined list.
     *
     * @param string $locale
     * @return bool
     */
    private static function isValidLocale(string $locale): bool {
        return isset(self::$VALID_LOCALES[$locale]);
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
