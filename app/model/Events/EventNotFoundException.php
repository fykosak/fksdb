<?php

namespace FKSDB\Events;

use FKSDB\Exceptions\NotFoundException;

/**
 * Class EventNotFoundException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventNotFoundException extends NotFoundException {
    /**
     * EventNotFoundException constructor.
     * @param \Exception|null $previous
     */
    public function __construct( \Exception $previous = null) {
        parent::__construct(_('Event not found.'), $previous);
    }
}
