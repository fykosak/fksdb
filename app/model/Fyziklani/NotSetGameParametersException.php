<?php

namespace FKSDB\Fyziklani;

use FKSDB\Exceptions\NotImplementedException;

/**
 * Class NotSetGameParametersException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotSetGameParametersException extends NotImplementedException {
    /**
     * NotSetGameParametersException constructor.
     * @param \Exception|null $previous
     */
    public function __construct(\Exception $previous = null) {
        parent::__construct(_('Herné parametre niesu nastavené'), $previous);
    }
}
