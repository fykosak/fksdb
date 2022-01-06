<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail;

class SendFailedException extends \RuntimeException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Failed to send an e-mail.'), null, $previous);
    }
}
