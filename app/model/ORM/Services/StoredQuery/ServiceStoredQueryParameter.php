<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQueryParameter extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_STORED_QUERY_PARAM, ModelStoredQueryParameter::class);
    }
}
