<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQueryParameter extends AbstractServiceSingle {
    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelStoredQueryParameter::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_STORED_QUERY_PARAM;
    }
}
