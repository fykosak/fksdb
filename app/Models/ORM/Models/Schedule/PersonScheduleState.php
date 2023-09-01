<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Utils\FakeStringEnum;

final class PersonScheduleState extends FakeStringEnum
{
    public const PARTICIPATED = 'participated';
    public const MISSED = 'missed';

    public static function cases(): array
    {
        return [
            new self(self::PARTICIPATED),
            new self(self::MISSED),
        ];
    }
}
