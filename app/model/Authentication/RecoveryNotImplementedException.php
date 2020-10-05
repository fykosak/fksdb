<?php

namespace FKSDB\Authentication;

/**
 * Class RecoveryNotImplementedException
 * *
 */
class RecoveryNotImplementedException extends RecoveryException {

    public function __construct(?\Throwable $previous = null) {
        $message = _('Přístup k účtu nelze obnovit.');
        $code = null;
        parent::__construct($message, $code, $previous);
    }
}
