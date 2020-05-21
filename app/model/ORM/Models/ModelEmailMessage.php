<?php

namespace FKSDB\ORM\Models;

use \Nette\Mail\Message;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Security\IResource;

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
 * @property-read \DateTimeInterface created
 * @property-read \DateTimeInterface sent
 */
class ModelEmailMessage extends AbstractModelSingle implements IResource {
    const STATE_SAVED = 'saved'; // uložená, na ďalšiu úpravu
    const STATE_WAITING = 'waiting'; //čaká na poslanie
    const STATE_SENT = 'sent'; // úspešné poslané (môže sa napr. ešte odraziť)
    const STATE_FAILED = 'failed'; // posielanie zlyhalo
    const STATE_CANCELED = 'canceled'; // posielanie zrušené

    const RESOURCE_ID = 'email_message';

    public function toMessage(): Message {
        $message = new Message();
        $message->setSubject($this->subject);
        $message->addTo($this->recipient);
        if (!is_null($this->blind_carbon_copy)) {
            $message->addBcc($this->blind_carbon_copy);
        }
        if (!is_null($this->carbon_copy)) {
            $message->addCc($this->carbon_copy);
        }
        $message->setFrom($this->sender);
        $message->addReplyTo($this->reply_to);
        $message->setHtmlBody($this->text);

        return $message;
    }

    public function getResourceId(): string {
        return static::RESOURCE_ID;
    }
}
