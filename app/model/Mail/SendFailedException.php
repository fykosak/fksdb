<?php

namespace FKSDB\Mail;

use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SendFailedException extends RuntimeException {

    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Nepodařilo se odeslat e-mail.'), null, $previous);
    }

}
