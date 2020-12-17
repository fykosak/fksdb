<?php

namespace FKSDB\Model\ORM\Models\StoredQuery;

use FKSDB\ORM\DeprecatedLazyModel;
use FKSDB\Model\ORM\Models\AbstractModelSingle;

/**
 * @todo Better (general) support for related collection setter.
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read int tag_type_id
 * @property-read string name
 * @property-read string description
 * @property-read int color
 */
class ModelStoredQueryTagType extends AbstractModelSingle {
    use DeprecatedLazyModel;
}
