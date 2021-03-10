<?php

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryExistsException extends RecoveryException {

    public function __construct(?\Throwable $previous = null) {
<<<<<<< HEAD
        parent::__construct(_('Account recovery is already in process.'), null, $previous);
=======
        parent::__construct(_('Account recovery in progress.'), null, $previous);
>>>>>>> master
    }

}
