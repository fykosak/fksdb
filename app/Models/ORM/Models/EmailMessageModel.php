<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Services\Exceptions\RejectedEmailException;
use FKSDB\Models\ORM\Services\UnsubscribedEmailService;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidStateException;
use Nette\Mail\Message;
use Nette\Security\Resource;
use Nette\Utils\DateTime;

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
 * @property-read DateTime $created
 * @property-read DateTime $sent
 * @property-read bool|int $priority
 * @property-read EmailMessageTopic $topic
 * @property-read Language $lang
 */
final class EmailMessageModel extends Model implements Resource
{
    public const RESOURCE_ID = 'emailMessage';

    /**
     * @throws RejectedEmailException
     * @throws BadTypeException
     */
    public function toMessage(
        UnsubscribedEmailService $unsubscribedEmailService,
        MailTemplateFactory $mailTemplateFactory
    ): Message {
        $message = new Message();
        $message->setSubject($this->subject);
        if (isset($this->recipient_person_id)) {
            $mail = $this->person->getInfo()->email;

            $preferenceType = $this->topic->mapToPreference();
            if ($preferenceType) {
                /** @var PersonEmailPreferenceModel|null $preference */
                $preference = $this->person->getMailPreferences()->where('option', $preferenceType)->fetch();
                if ($preference && !$preference->value) {
                    throw new RejectedEmailException();
                }
            }
        } elseif (isset($this->recipient)) {
            $mail = $this->recipient;
            $unsubscribedEmailService->checkEmail($mail);
        } else {
            throw new InvalidStateException('Recipient organizer person_id is required');
        }

        $message->addTo($mail);

        if (!is_null($this->blind_carbon_copy)) {
            $message->addBcc($this->blind_carbon_copy);
        }
        if (!is_null($this->carbon_copy)) {
            $message->addCc($this->carbon_copy);
        }
        $message->setFrom($this->sender);
        $message->addReplyTo($this->reply_to);
        $text = $mailTemplateFactory->addContainer($this);
        $message->setHtmlBody($text);

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
                $value = EmailMessageState::from($value);
                break;
        }
        return $value;
    }
}
