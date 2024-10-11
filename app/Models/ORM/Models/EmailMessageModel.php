<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model\Model;
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
 * @property-read string|null $inner_text
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
            case 'topic':
                $value = EmailMessageTopic::from($value);
                break;
            case 'lang':
                $value = Language::from($value);
                break;
        }
        return $value;
    }
}
