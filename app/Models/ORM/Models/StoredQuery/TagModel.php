<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\StoredQuery;

use Fykosak\NetteORM\Model\Model;

/**
 * @todo Better (general) support for related collection setter.
 * @property-read int $tag_type_id
 * @property-read string $name
 * @property-read string $description
 * @property-read int $color
 * @property-read TagTypeModel $tag_type
 */
final class TagModel extends Model
{
}
