<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Utils\FakeStringEnum;

final class PresenterRole extends FakeStringEnum
{
    public const ORG = 'org';
    public const CONTESTANT = 'contestant';
    public const ALL = 'all';
    public const SELECTED = 'selected';

    public static function cases(): array
    {
        return [
            new self(self::ORG),
            new self(self::CONTESTANT),
            new self(self::ALL),
            new self(self::SELECTED),
        ];
    }
}
