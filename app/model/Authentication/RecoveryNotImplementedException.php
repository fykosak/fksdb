<?php

namespace FKSDB\Authentication;

/**
 * Class RecoveryNotImplementedException
 * *
 */
class RecoveryNotImplementedException extends RecoveryException {

    /**
     * RecoveryNotImplementedException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Přístup k účtu nelze obnovit.');
        $code = null;
        parent::__construct($message, $code, $previous);
    }
}
