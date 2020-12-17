<?php

namespace FKSDB\Model\Fyziklani;

use FKSDB\Model\Exceptions\NotImplementedException;

/**
 * Class NotSetGameParametersException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotSetGameParametersException extends NotImplementedException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Herné parametre niesu nastavené'), $previous);
    }
}
