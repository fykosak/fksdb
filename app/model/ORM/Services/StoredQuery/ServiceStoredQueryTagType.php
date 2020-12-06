<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTagType extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_STORED_QUERY_TAG_TYPE, ModelStoredQueryTagType::class);
    }
}
