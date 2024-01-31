<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\Logging\Message;

final class BehaviorType extends FakeStringEnum
{

    public const SUCCESS = Message::LVL_SUCCESS;
    public const WARNING = Message::LVL_WARNING;
    public const DANGEROUS = Message::LVL_ERROR;
    public const PRIMARY = Message::LVL_PRIMARY;
    public const DEFAULT = 'secondary';

    public static function cases(): array
    {
        return [
            new self(self::SUCCESS),
            new self(self::WARNING),
            new self(self::DANGEROUS),
            new self(self::DEFAULT),
            new self(self::PRIMARY),
        ];
    }
}
