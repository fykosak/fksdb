<?php

namespace FKSDB\Models\Mail;

use RuntimeException;

class SendFailedException extends RuntimeException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Failed to send an e-mail.'), null, $previous);
    }

}
