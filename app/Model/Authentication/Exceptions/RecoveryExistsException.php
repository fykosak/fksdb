<?php

namespace FKSDB\Model\Authentication\Exceptions;

class RecoveryExistsException extends RecoveryException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Account recovery in progress.'), null, $previous);
    }
}
