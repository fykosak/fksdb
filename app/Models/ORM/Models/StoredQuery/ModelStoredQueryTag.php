<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\StoredQuery;

use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @todo Better (general) support for related collection setter.
 * @property-read int tag_type_id
 * @property-read string name
 * @property-read string description
 * @property-read int color
 * @property-read ModelStoredQueryTagType tag_type
 */
class ModelStoredQueryTag extends Model
{
}
