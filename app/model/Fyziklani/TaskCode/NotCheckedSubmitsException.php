<?php

namespace FKSDB\model\Fyziklani;

use Nette\Application\BadRequestException;

/**
 * Class NotCheckedSubmitsException
 * @package FKSDB\model\Fyziklani
 */
class NotCheckedSubmitsException extends BadRequestException {
    /**
     * NotCheckedSubmitsException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|NULL $previous
     */
    public function __construct($message = '', $code = 0, \Exception $previous = NULL) {
        parent::__construct(_('Team has non checked submits'), $code, $previous);
    }
}
