<?php

namespace FKSDB\ORM\Models;

use DateTime;
use \Nette\Mail\Message;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class ModelEmailMessage
 * @package FKSDB\ORM\Models
 * @property-read int email_message_id`
 * @property-read string recipient
 * @property-read string sender
 * @property-read string reply_to
 * @property-read string subject
 * @property-read string|null carbon_copy
 * @property-read string|null blind_carbon_copy
 * @property-read string text
 * @property-read string state
 * @property-read DateTime created
 * @property-read DateTime sent
 */
class ModelEmailMessage extends AbstractModelSingle {
    const STATE_SAVED = 'saved';
    const STATE_WAITING = 'waiting';
    const STATE_SENT = 'sent';
    const STATE_FAILED = 'failed';
    const STATE_CANCELED = 'canceled';

    /**
     * @return Message
     */
    public function toMessage(): Message {
        $message = new Message();
        $message->setSubject($this->subject);
        $message->addTo($this->recipient);
        if (!is_null($this->blind_carbon_copy)) {
            $message->addBcc($this->blind_carbon_copy);
        }
        if (is_null($this->carbon_copy)) {
            $message->addCc($this->carbon_copy);
        }
        $message->setFrom($this->sender);
        $message->addReplyTo($this->reply_to);
        $message->setHtmlBody($this->text);

        return $message;
    }
}
