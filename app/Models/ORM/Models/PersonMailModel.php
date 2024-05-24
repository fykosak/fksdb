<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

/**
 * @property-read int $person_mail_id
 * @property-read string $mail_type
 * @property-read int $person_id
 * @property-read PersonModel $person
 */
final class PersonMailModel extends Model implements Resource
{
    public const RESOURCE_ID = 'personMail';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
