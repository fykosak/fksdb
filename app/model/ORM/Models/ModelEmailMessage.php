<?php

namespace FKSDB\ORM\Models;

use DateTime;
use \Nette\Mail\Message;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Security\IResource;
use Tracy\Debugger;

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
class ModelEmailMessage extends AbstractModelSingle implements IResource {
    const STATE_SAVED = 'saved'; // uložená, na ďalšiu úpravu
    const STATE_WAITING = 'waiting'; //čaká na poslanie
    const STATE_SENT = 'sent'; // úspešné poslané (môže sa napr. ešte odraziť)
    const STATE_FAILED = 'failed'; // posielanie zlyhalo
    const STATE_CANCELED = 'canceled'; // posielanie zrušené

    const RESOURCE_ID = 'email_message';

    /**
     * @return Message
     */
    public function toMessage(): Message {
        $message = new Message();
        $message->setSubject($this->subject);

        foreach (\mailparse_rfc822_parse_addresses($this->recipient) as list('display' => $name, 'address' => $address)) {
            $message->addTo($address, $name);
        }

        list('display' => $senderName, 'address' => $senderAddress) = \mailparse_rfc822_parse_addresses($this->sender)[0];
        $message->setFrom($senderAddress, $senderName);

        list('display' => $replyToName, 'address' => $replyToAddress) = \mailparse_rfc822_parse_addresses($this->reply_to)[0];
        $message->addReplyTo($replyToAddress, $replyToName);

        if (!is_null($this->blind_carbon_copy)) {
            foreach (\mailparse_rfc822_parse_addresses($this->blind_carbon_copy) as list('display' => $name, 'address' => $address)) {
                $message->addBcc($address, $name);
            }
        }
        if (!is_null($this->carbon_copy)) {
            foreach (\mailparse_rfc822_parse_addresses($this->carbon_copy) as list('display' => $name, 'address' => $address)) {
                $message->addCc($address, $name);
            }
        }

        $message->setHtmlBody($this->text);

        return $message;
    }

    /**
     * @return string
     */
    public function getResourceId() {
        return static::RESOURCE_ID;
    }
}
