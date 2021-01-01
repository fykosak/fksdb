<?php

namespace FKSDB\Models\Fyziklani\Submit;

use Throwable;

/**
 * Class PointsMismatchException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PointsMismatchException extends TaskCodeException {

    public function __construct(int $code = 0, ?Throwable $previous = null) {
        parent::__construct(_('Points mismatch'), $code, $previous);
    }
}
