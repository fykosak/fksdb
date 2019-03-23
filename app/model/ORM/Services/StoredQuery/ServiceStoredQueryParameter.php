<?php

namespace FKSDB\ORM\Services\StoredQuery;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQueryParameter extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY_PARAM;
    protected $modelClassName = 'FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter';

}
