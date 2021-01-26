<?php

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryExistsException extends RecoveryException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Account recovery is already in process.'), null, $previous);
    }

}
