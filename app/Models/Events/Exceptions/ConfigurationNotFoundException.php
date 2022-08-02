<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Exceptions;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\InvalidStateException;

class ConfigurationNotFoundException extends InvalidStateException
{
    public function __construct(EventModel $event, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                _('Configuration for event %s (%d-%s) not found'),
                $event->name,
                $event->event_type_id,
                $event->event_year
            ),
            $code,
            $previous
        );
    }
}
