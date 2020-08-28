<?php

namespace FKSDB\Entity;

use FKSDB\Exceptions\NotFoundException;

/**
 * Class ModelNotFoundException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelNotFoundException extends NotFoundException {
    /**
     * ModelNotFoundException constructor.
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = null, \Exception $previous = null) {
        parent::__construct($message ?? _('Model not found'), $previous);
    }

}
