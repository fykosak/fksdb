<?php

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryNotImplementedException extends RecoveryException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('The account recovery is not possible.'), null, $previous);
    }
}
