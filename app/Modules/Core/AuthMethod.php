<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Models\Utils\FakeStringEnum;

class AuthMethod extends FakeStringEnum
{
    public const LOGIN = 'login';
    public const HTTP = 'http';
    public const TOKEN = 'token';

    public static function cases(): array
    {
        return [
            new self(self::TOKEN),
            new self(self::HTTP),
            new self(self::LOGIN),
        ];
    }
}
