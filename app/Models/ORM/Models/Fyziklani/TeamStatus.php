<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
class TeamStatus
{
    public string $value;

    public function __construct(string $status)
    {
        $this->value = $status;
    }

    public static function tryFrom(?string $status): ?self
    {
        return $status ? new self($status) : null;
    }
}
