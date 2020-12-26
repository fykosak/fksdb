<?php

namespace FKSDB\Models\Fyziklani;

use FKSDB\Models\Exceptions\NotImplementedException;

/**
 * Class NotSetGameParametersException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotSetGameParametersException extends NotImplementedException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Herné parametre niesu nastavené'), $previous);
    }
}
