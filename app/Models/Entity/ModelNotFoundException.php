<?php

namespace FKSDB\Models\Entity;

use FKSDB\Models\Exceptions\NotFoundException;

class ModelNotFoundException extends NotFoundException
{

    public function __construct(?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct($message ?? _('Model not found'), $previous);
    }
}
