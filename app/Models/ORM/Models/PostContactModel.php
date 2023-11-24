<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $post_contact_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $address_id
 * @property-read AddressModel $address
 * @property-read string $type
 * TODO to enum!!
 */
final class PostContactModel extends Model
{
}
