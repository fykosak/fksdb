<?php

namespace FKSDB\Entity;

use Nette\InvalidStateException;
use Throwable;

/**
 * Class CannotAccessModelException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CannotAccessModelException extends InvalidStateException {
    /**
     * CannotAccessModelException constructor.
     * @param string $modelClassName
     * @param $model
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $modelClassName, $model, $code = 0, Throwable $previous = null) {
        parent::__construct(sprintf(_('Can not access model %s from %s'), $modelClassName, get_class($model)), $code, $previous);
    }
}
