<?php


namespace FKSDB\Authentication;

/**
 * Class RecoveryExistsException
 * *
 */
class RecoveryExistsException extends RecoveryException {

    /**
     * RecoveryExistsException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Obnova účtu již probíhá.');
        $code = null;
        parent::__construct($message, $code, $previous);
    }

}
