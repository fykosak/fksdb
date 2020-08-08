<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQueryParameter extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function getModelClassName(): string {
        return ModelStoredQueryParameter::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_STORED_QUERY_PARAM;
    }
}
