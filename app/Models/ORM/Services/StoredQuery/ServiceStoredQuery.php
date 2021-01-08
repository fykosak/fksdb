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
        if (!$qid) {
            return null;
        }
        /** @var ModelStoredQuery $result */
        $result = $this->getTable()->where('qid', $qid)->fetch();
        return $result ?: null;
    }

    /**
     * @param int|array|null $tagTypeId
     * @return TypedTableSelection
     */
    public function findByTagType($tagTypeId): ?TypedTableSelection {
        if (!$tagTypeId) {
            return null;
        }
        $queryIds = $this->serviceStoredQueryTag->findByTagTypeId($tagTypeId)->fetchPairs('query_id', 'query_id');
        return $this->getTable()->where('query_id', $queryIds);
    }
}
