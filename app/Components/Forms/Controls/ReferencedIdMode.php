<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Models\Utils\FakeStringEnum;

final class ReferencedIdMode extends FakeStringEnum
{
    public const NORMAL = 'NORMAL';
    public const FORCE = 'FORCE';
    public const ROLLBACK = 'ROLLBACK';

    public static function cases(): array
    {
        return [
            new self(self::NORMAL),
            new self(self::FORCE),
            new self(self::ROLLBACK),
        ];
    }
}
