<?php

namespace FKSDB\Fyziklani;

use Nette\Application\BadRequestException;

/**
 * Class NotCheckedSubmitsException
 * @package FKSDB\model\Fyziklani
 */
class NotCheckedSubmitsException extends BadRequestException {
    /**
     * NotCheckedSubmitsException constructor.
     * @param int $code
     * @param \Exception|NULL $previous
     */
    public function __construct($code = 0, \Exception $previous = NULL) {
        parent::__construct(_('Team has non checked submits'), $code, $previous);
    }
}
