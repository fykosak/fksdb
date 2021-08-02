<?php

namespace FKSDB\Models\ORM\Models\StoredQuery;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @todo Better (general) support for related collection setter.
 * @property-read int tag_type_id
 * @property-read string name
 * @property-read string description
 * @property-read int color
 * @property-read ActiveRow tag_type
 */
class ModelStoredQueryTag extends AbstractModel
{

    public function getTagType(): ModelStoredQueryTagType
    {
        return ModelStoredQueryTagType::createFromActiveRow($this->tag_type);
    }
}
