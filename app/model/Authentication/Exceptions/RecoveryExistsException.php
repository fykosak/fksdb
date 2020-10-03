<?php

namespace FKSDB\Authentication;

class RecoveryExistsException extends RecoveryException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Obnova účtu již probíhá.'), null, $previous);
    }

}
