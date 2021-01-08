<?php

namespace FKSDB\Models\Entity;

use Nette\Application\BadRequestException;
use Throwable;

/**
 * Class CannotAccessModelException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CannotAccessModelException extends BadRequestException {

    public function __construct(string $modelClassName, object $model, int $code = 0, ?Throwable $previous = null) {
        parent::__construct(sprintf(_('Can not access model %s from %s'), $modelClassName, get_class($model)), $code, $previous);
    }
}
