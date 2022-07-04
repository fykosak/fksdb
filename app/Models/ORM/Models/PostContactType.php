<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

class PostContactType
{
    public const DELIVERY = 'D';
    public const PERMANENT = 'P';

    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
