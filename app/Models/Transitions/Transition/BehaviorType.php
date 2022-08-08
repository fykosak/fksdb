<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use Fykosak\Utils\Logging\Message;
use Nette\InvalidArgumentException;

final class BehaviorType
{
    public string $value;

    public const SUCCESS = Message::LVL_SUCCESS;
    public const WARNING = Message::LVL_WARNING;
    public const DANGEROUS = Message::LVL_ERROR;
    public const PRIMARY = Message::LVL_PRIMARY;
    public const DEFAULT = 'secondary';

    public function __construct(string $value)
    {
        if (!in_array($value, self::cases())) {
            throw new InvalidArgumentException(sprintf('Behavior type %s not allowed', $value));
        }
        $this->value = $value;
    }

    public static function cases(): array
    {
        return [
            self::SUCCESS,
            self::WARNING,
            self::DANGEROUS,
            self::DEFAULT,
            self::PRIMARY,
        ];
    }
}
