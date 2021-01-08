<?php

namespace FKSDB\Model\ORM\Models\StoredQuery;

use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\DeprecatedLazyModel;
use Nette\Database\Table\ActiveRow;

/**
 * @todo Better (general) support for related collection setter.
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read int tag_type_id
 * @property-read string name
 * @property-read string description
 * @property-read int color
 * @property-read ActiveRow tag_type
 */
class ModelStoredQueryTag extends AbstractModelSingle {
    use DeprecatedLazyModel;

    public function getTagType(): ModelStoredQueryTagType {
        return ModelStoredQueryTagType::createFromActiveRow($this->tag_type);
    }
}
