<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Utils\FakeStringEnum;

class PresenterRole extends FakeStringEnum
{
    public const ORGANISER = 'organiser';
    public const CONTESTANT = 'contestant';
    public const ALL = 'all';
    public const SELECTED = 'selected';

    public static function cases(): array
    {
        return [
            new self(self::ORGANISER),
            new self(self::CONTESTANT),
            new self(self::ALL),
            new self(self::SELECTED),
        ];
    }
}
