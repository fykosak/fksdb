<?php

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryNotImplementedException extends RecoveryException {
    public function __construct(?\Throwable $previous = null) {
<<<<<<< HEAD
        parent::__construct(_('The account recovery is not possible.'), null, $previous);
=======
        parent::__construct(_('Account cannot be recovered.'), null, $previous);
>>>>>>> master
    }
}
