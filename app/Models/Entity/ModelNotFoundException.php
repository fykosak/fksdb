<?php

namespace FKSDB\Models\Entity;

use FKSDB\Models\Exceptions\NotFoundException;

/**
 * Class ModelNotFoundException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelNotFoundException extends NotFoundException {

    public function __construct(?string $message = null, ?\Throwable $previous = null) {
        parent::__construct($message ?? _('Model not found'), $previous);
    }

}