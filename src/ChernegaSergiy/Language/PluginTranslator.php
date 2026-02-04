<?php

declare(strict_types=1);

namespace ChernegaSergiy\Language;

use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class PluginTranslator implements TranslatorInterface {

    /** @var array<string, array<string, string>> */
    private array $translations = [];
    private LocaleResolverInterface $localeResolver;
    private string $defaultLocale;

    /**
     * @param Language[] $languages
     */
    public function __construct(
        private PluginBase $plugin,
        array $languages,
        ?LocaleResolverInterface $localeResolver = null,
        string $defaultLocale = "en_US"
    ) {
        $this->localeResolver = $localeResolver ?? new DefaultLocaleResolver();
        $this->defaultLocale = $defaultLocale;

        foreach ($languages as $language) {
            if (!$language instanceof Language) {
                throw new InvalidArgumentException("Expected an array of Language objects.");
            }
            $this->translations[$language->getLocale()] = $this->flattenArray($language->getTranslations());
        }

        if (!isset($this->translations[$this->defaultLocale]) && !empty($this->translations)) {
            // If default locale is not loaded, fallback to the first available one to avoid empty translations
            $available = array_keys($this->translations);
            $this->defaultLocale = reset($available);
        }
    }

    public function translateFor(?CommandSender $sender, string $key, array $args = []): string {
        $locale = $sender instanceof Player ? $this->localeResolver->resolve($sender) : $this->defaultLocale;
        return $this->translate($locale, $key, $args, $sender);
    }

    public function translate(string $locale, string $key, array $args = [], ?CommandSender $sender = null): string {
        $translation = $this->translations[$locale][$key] ?? $this->translations[$this->defaultLocale][$key] ?? $key;

        // Process internal placeholders
        foreach ($args as $placeholder => $value) {
            $translation = str_replace('%' . $placeholder . '%', (string)$value, $translation);
        }

        // Process PlaceholderAPI placeholders if possible
        if ($sender instanceof Player) {
            $pluginManager = $this->plugin->getServer()->getPluginManager();
            $placeholderApi = $pluginManager->getPlugin("PlaceholderAPI");
            if ($placeholderApi !== null && $placeholderApi->isEnabled()) {
                /** @noinspection PhpUndefinedMethodInspection */
                $translation = $placeholderApi->parsePlaceholders($translation, $sender);
            }
        }

        return $translation;
    }

    /**
     * Flattens a multi-dimensional array into a single level array using dot notation.
     *
     * @param array<string, mixed> $array
     * @param string               $prefix
     * @return array<string, string>
     */
    private function flattenArray(array $array, string $prefix = ''): array {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? (string)$key : $prefix . '.' . $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = (string)$value;
            }
        }
        return $result;
    }
}