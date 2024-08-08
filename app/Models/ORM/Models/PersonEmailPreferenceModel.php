<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Utils\DateTime;

/**
 * @property-read int $email_preference_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read PersonEmailPreferenceOption $option
 * @property-read bool $value
 * @property-read DateTime $created
 */
class PersonEmailPreferenceModel extends Model
{
}
