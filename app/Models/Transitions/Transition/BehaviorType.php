<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use Fykosak\Utils\Logging\Message;
use Nette\InvalidArgumentException;

class BehaviorType
{
    public string $value;

    public const TYPE_SUCCESS = Message::LVL_SUCCESS;
    public const TYPE_WARNING = Message::LVL_WARNING;
    public const TYPE_DANGEROUS = Message::LVL_ERROR;
    public const TYPE_PRIMARY = Message::LVL_PRIMARY;
    public const TYPE_DEFAULT = 'secondary';

    protected const AVAILABLE_BEHAVIOR_TYPE = [
        self::TYPE_SUCCESS,
        self::TYPE_WARNING,
        self::TYPE_DANGEROUS,
        self::TYPE_DEFAULT,
        self::TYPE_PRIMARY,
    ];

    public function __construct(string $value)
    {
        if (!in_array($value, static::AVAILABLE_BEHAVIOR_TYPE)) {
            throw new InvalidArgumentException(sprintf('Behavior type %s not allowed', $value));
        }
        $this->value = $value;
    }
}
