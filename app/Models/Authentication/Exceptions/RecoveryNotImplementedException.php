<?php

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryNotImplementedException extends RecoveryException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Account cannot be recovered.'), null, $previous);
    }
}
