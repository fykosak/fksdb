<?php

namespace FKSDB\Model\ORM\Services\StoredQuery;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use FKSDB\Model\ORM\Services\DeprecatedLazyService;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQueryParameter extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_STORED_QUERY_PARAM, ModelStoredQueryParameter::class);
    }
}
