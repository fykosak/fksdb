<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQuery extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelStoredQuery::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_STORED_QUERY;
    }

    private ServiceStoredQueryTag $serviceStoredQueryTag;

    /**
     * FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery constructor.
     * @param Context $context
     * @param ServiceStoredQueryTag $serviceStoredQueryTag
     * @param IConventions $conventions
     */
    public function __construct(Context $context, ServiceStoredQueryTag $serviceStoredQueryTag, IConventions $conventions) {
        parent::__construct($context, $conventions);
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
    public function findByTagType($tagTypeId) {
        if (!$tagTypeId) {
            return null;
        }
        $queryIds = $this->serviceStoredQueryTag->findByTagTypeId($tagTypeId)->fetchPairs('query_id', 'query_id');
        return $this->getTable()->where('query_id', $queryIds);
    }
}
