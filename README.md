# libLanguage Virion

[![Poggit CI](https://poggit.pmmp.io/ci.shield/newlandpe/libLanguage/libLanguage)](https://poggit.pmmp.io/ci/newlandpe/libLanguage/libLanguage)

`libLanguage` is a powerful and flexible language abstraction library designed for PocketMine-MP plugins. It provides a convenient way to manage multiple language translations within your plugin, allowing for easy localization of messages, commands, and other text-based content.

## Installation

To include `libLanguage` in your plugin, add it as a virion dependency in your `poggit.yml` file:

```yaml
# .poggit.yml
--- # Poggit-CI Manifest.yml
build-by-default: true
branches:
- main
projects:
  MyPlugin:
    path: ""
    type: "plugin"
    libs:
      - src: newlandpe/libLanguage/libLanguage
        version: ^0.1 # Use the latest version or a specific one
```

> [!WARNING]
> This library requires the PHP `intl` extension for ICU MessageFormat support. Ensure it's installed on your server (usually available by default in most PHP distributions).

## Architecture

The `libLanguage` virion is built around a modular and extensible architecture, ensuring robust and conflict-free translation management for individual plugins.

- **`PluginTranslator`**: The primary class used by your plugin. It handles translations, placeholder replacement (including PlaceholderAPI), and locale resolution.
- **`LanguageLoader`**: A utility class to easily load translation files (YAML/JSON) from your plugin's resources or data folder. Only files with valid locale names (e.g., `en_US.yml`, `zh-CN.json`) are loaded.
- **`LocaleResolverInterface` & `DefaultLocaleResolver`**: Defines how a player's locale is determined. By default, it uses `Player::getLocale()`.
- **`Language`**: A simple data class representing a specific locale and its translations.

This architecture ensures per-plugin language isolation, preventing conflicts between translations from different plugins.

## Basic Usage

### 1. Loading Your Plugin's Languages

In your plugin's `onEnable()` method, you can use `LanguageLoader` to load your translations and initialize the `PluginTranslator`.

```php
<?php

namespace MyPlugin;

use ChernegaSergiy\Language\LanguageLoader;
use ChernegaSergiy\Language\PluginTranslator;
use ChernegaSergiy\Language\TranslatorInterface;
use pocketmine\plugin\PluginBase;

class MyPlugin extends PluginBase {

    private TranslatorInterface $translator;

    public function onEnable(): void {
        // Save your language files from resources to data folder
        $this->saveResource("languages/en_US.yml");
        $this->saveResource("languages/uk_UA.yml");

        // Load all languages from the directory
        $languages = LanguageLoader::loadFromDirectory($this->getDataFolder() . "languages");

        // Initialize your PluginTranslator instance
        $this->translator = new PluginTranslator(
            $this, 
            $languages, 
            null, // Use default LocaleResolver (Player::getLocale())
            "en_US" // Default fallback locale
        );
    }

    public function getTranslator(): TranslatorInterface {
        return $this->translator;
    }
}
```

### 2. Translating Messages

Once you have your `PluginTranslator` instance, you can use its `translateFor()` or `translate()` methods. It supports nested keys using dot notation (e.g., `commands.help.title`), ICU MessageFormat for advanced formatting (e.g., plurals), and legacy `%key%` placeholders.

```php
<?php

namespace MyPlugin;

use pocketmine\command\CommandSender;

// ... (inside your command or event handler)

public function someMethod(CommandSender $sender, int $itemCount) {
    // Simple legacy placeholder
    $message1 = $this->translator->translateFor(
        $sender,
        "item.count",
        ["count" => $itemCount]
    );
    
    // Advanced ICU pluralization
    $message2 = $this->translator->translateFor(
        $sender,
        "item.plural",
        ["count" => $itemCount]
    );
    $sender->sendMessage($message1);
    $sender->sendMessage($message2);
}
```

In your language files (e.g., `en_US.yml`):

```yaml
item:
  count: "You have %count% items."
  plural: "{count, plural, one{You have # item.} other{You have # items.}}"
```

## API Reference

### `ChernegaSergiy\Language\PluginTranslator`

The concrete implementation of `TranslatorInterface` used by plugins.

- `__construct(PluginBase $plugin, array $languages, ?LocaleResolverInterface $localeResolver = null, string $defaultLocale = "en_US")`: Constructor. Initializes the translator with plugin-specific languages, a locale resolver, and a default locale.
- `translateFor(?CommandSender $sender, string $key, array $args = []): string`: Translates a message for a `CommandSender`.
- `translate(string $locale, string $key, array $args = [], ?CommandSender $sender = null): string`: Translates a message for a specific locale. Supports ICU MessageFormat (e.g., `{count, plural, one{# item} other{# items}}`) for advanced pluralization and formatting, as well as legacy `%key%` placeholders for backward compatibility.

### `ChernegaSergiy\Language\LanguageLoader`

A utility class to load language files.

- `static loadFromDirectory(string $directory): array`: Loads all `.yml`, `.yaml`, and `.json` files with valid locale names from a directory and returns an array of `Language` objects.

### `ChernegaSergiy\Language\LocaleResolverInterface`

An interface for resolving a player's locale.

- `resolve(Player $player): string`: Resolves and returns the locale string for a given player.

### `ChernegaSergiy\Language\Language`

A data class representing a single language's translations.

- `__construct(string $locale, array $translations)`: Creates a new language instance.
- `getLocale(): string`: Returns the locale string (e.g., "en_US").
- `getTranslations(): array`: Returns the raw array of translations for this language.

## Contributing

Contributions are welcome and appreciated! Here's how you can contribute:

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please make sure to update tests as appropriate and adhere to the existing coding style.

## License

This library is licensed under the CSSM Unlimited License v2.0 (CSSM-ULv2). Please note that this is a custom license. See the [LICENSE](LICENSE) file for details.
