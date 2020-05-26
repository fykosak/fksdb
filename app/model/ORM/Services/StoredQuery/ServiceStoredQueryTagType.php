<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTagType extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelStoredQueryTagType::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_STORED_QUERY_TAG_TYPE;
    }
}
