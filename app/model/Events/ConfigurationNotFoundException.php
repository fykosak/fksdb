<?php

namespace FKSDB\Events;

use FKSDB\ORM\Models\ModelEvent;
use Nette\InvalidStateException;
use Throwable;

class ConfigurationNotFoundException extends InvalidStateException {
    /**
     * ConfigurationNotFoundException constructor.
     * @param ModelEvent $event
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(ModelEvent $event, $code = 0, Throwable $previous = null) {
        parent::__construct(sprintf(_('Configuration for event %s (%d-%s) not found'), $event->name, $event->event_type_id, $event->event_year), $code, $previous);
    }
}
