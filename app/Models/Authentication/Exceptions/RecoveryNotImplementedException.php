<?php

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryNotImplementedException extends RecoveryException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Přístup k účtu nelze obnovit.'), null, $previous);
    }
}
