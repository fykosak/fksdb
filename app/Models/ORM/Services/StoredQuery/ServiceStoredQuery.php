<?php

namespace FKSDB\Models\ORM\Services\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQuery extends AbstractServiceSingle {

    private ServiceStoredQueryTag $serviceStoredQueryTag;

    public function __construct(string $tableName, string $className, Explorer $explorer, ServiceStoredQueryTag $serviceStoredQueryTag, Conventions $conventions) {
        parent::__construct($tableName, $className, $explorer, $conventions);
        $this->serviceStoredQueryTag = $serviceStoredQueryTag;
    }

    public function findByQid(string $qid): ?ModelStoredQuery {
        /** @var ModelStoredQuery $result */
        $result = $this->getTable()->where('qid', $qid)->fetch();
        return $result;
    }

    public function findByTagType(array $tagTypeIds): ?TypedTableSelection {
        return $this->getTable()->where(':stored_query_tag.tag_type_id', $tagTypeIds);
    }
}
