<?php

namespace Mail;

use Nette\Mail\Message;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SendFailedException extends RuntimeException {

    /**
     * @var Message
     */
    public $message;

    public function __construct($previous = null) {
        $message = _('Nepodařilo se odeslat e-mail.');
        parent::__construct($message, null, $previous);
    }

}
