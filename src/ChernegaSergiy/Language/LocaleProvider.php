<?php

declare(strict_types=1);

namespace ChernegaSergiy\Language;

interface LocaleProvider {

    public function getLocaleResolver(): LocaleResolverInterface;
}
