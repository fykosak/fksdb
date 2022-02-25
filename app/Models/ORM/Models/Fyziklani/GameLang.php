<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

class GameLang
{
    public const CS = 'cs';
    public const EN = 'en';

    public string $value;

    public function __construct(string $lang)
    {
        $this->value = $lang;
    }

    public static function tryFrom(?string $lang): ?self
    {
        if (is_null($lang)) {
            return null;
        }
        return new self($lang);
    }
}
