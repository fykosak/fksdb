<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\Utils\FakeStringEnum;

class ResolutionMode extends FakeStringEnum
{
    public const OVERWRITE = 'overwrite';
    public const KEEP = 'keep';
    public const EXCEPTION = 'exception';

    public static function cases(): array
    {
        return [
            new self(self::OVERWRITE),
            new self(self::KEEP),
            new self(self::EXCEPTION),
        ];
    }
}
