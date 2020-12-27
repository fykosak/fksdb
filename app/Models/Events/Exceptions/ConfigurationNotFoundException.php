<?php

namespace FKSDB\Models\Events\Exceptions;

use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\InvalidStateException;
use Throwable;

class ConfigurationNotFoundException extends InvalidStateException {

    public function __construct(ModelEvent $event, int $code = 0, ?Throwable $previous = null) {
        parent::__construct(sprintf(_('Configuration for event %s (%d-%s) not found'), $event->name, $event->event_type_id, $event->event_year), $code, $previous);
    }
}
