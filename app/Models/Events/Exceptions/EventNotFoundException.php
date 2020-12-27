<?php

namespace FKSDB\Models\Events\Exceptions;

use FKSDB\Models\Exceptions\NotFoundException;

/**
 * Class EventNotFoundException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventNotFoundException extends NotFoundException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Event not found.'), $previous);
    }
}
