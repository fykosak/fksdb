<?php

namespace FKSDB\Model\ORM\Services\StoredQuery;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\StoredQuery\ModelStoredQueryTagType;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
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
