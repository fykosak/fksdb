<?php

namespace FKSDB\ORM\Services\StoredQuery;
use AbstractServiceSingle;
use DbNames;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTagType extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY_TAG_TYPE;
    protected $modelClassName = 'FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType';

}
