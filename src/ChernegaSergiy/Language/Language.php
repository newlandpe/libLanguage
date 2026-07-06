<?php

declare(strict_types=1);

namespace ChernegaSergiy\Language;

final class Language {

    public function __construct(
        private readonly string $locale,
        private readonly array $translations
    ) {}

    public function getLocale(): string {
        return $this->locale;
    }

    public function getTranslations(): array {
        return $this->translations;
    }
}
