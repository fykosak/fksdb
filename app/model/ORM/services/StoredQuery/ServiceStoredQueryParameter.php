<?php

namespace FKSDB\ORM\Services\StoredQuery;
use AbstractServiceSingle;
use DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQueryParameter extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY_PARAM;
    protected $modelClassName = 'FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter';

}
