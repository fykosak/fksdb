<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Services\Exceptions\UnsubscribedEmailException;
use FKSDB\Models\ORM\Services\UnsubscribedEmailService;
use Fykosak\NetteORM\Model;
use Nette\InvalidStateException;
use Nette\Mail\Message;
use Nette\Security\Resource;

/**
 * @property-read int $email_message_id`
 * @property-read string $recipient
 * @property-read int|null $recipient_person_id
 * @property-read PersonModel|null $person
 * @property-read string $sender
 * @property-read string $reply_to
 * @property-read string $subject
 * @property-read string|null $carbon_copy
 * @property-read string|null $blind_carbon_copy
 * @property-read string $text
 * @property-read EmailMessageState $state
 * @property-read \DateTimeInterface $created
 * @property-read \DateTimeInterface $sent
 */
final class EmailMessageModel extends Model implements Resource
{
    public const RESOURCE_ID = 'emailMessage';

    /**
     * @throws UnsubscribedEmailException
     */
    public function toMessage(UnsubscribedEmailService $unsubscribedEmailService): Message
    {
        $message = new Message();
        $message->setSubject($this->subject);
        if (isset($this->recipient_person_id)) {
            if (isset($this->recipient) && $this->person->getInfo()->email !== $this->recipient) {
                throw new InvalidStateException('Recipient and person\'s email do not match');
            }
            $mail = $this->person->getInfo()->email;
        } elseif (isset($this->recipient)) {
            $mail = $this->recipient;
        } else {
            throw new InvalidStateException('Recipient organizer person_id is required');
        }
        $unsubscribedEmailService->checkEmail($mail);
        $message->addTo($mail);

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

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @return EmailMessageState|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = EmailMessageState::tryFrom($value);
                break;
        }
        return $value;
    }
}
