<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\Exceptions\NotFoundException;

class TaskNotFoundException extends NotFoundException
{
    public function __construct(?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct($message ?? _('Task not found.'), $previous);
    }
}
