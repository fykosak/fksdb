<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use Nette;
use Nette\Database\Connection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQuery extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY;
    protected $modelClassName = 'FKSDB\ORM\Models\StoredQuery\ModelStoredQuery';

    /**
     *
     * @var ServiceStoredQueryTag
     */
    private $serviceStoredQueryTag;

    /**
     * FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery constructor.
     * @param Connection $connection
     * @param ServiceStoredQueryTag $serviceStoredQueryTag
     */
    public function __construct(Connection $connection, ServiceStoredQueryTag $serviceStoredQueryTag) {
        parent::__construct($connection);
        $this->serviceStoredQueryTag = $serviceStoredQueryTag;
    }

    /**
     * Syntactic sugar.
     *
     * @param string|null $qid
     * @return \FKSDB\ORM\Models\StoredQuery\ModelStoredQuery|null
     */
    public function findByQid($qid) {
        if (!$qid) {
            return null;
        }
        $result = $this->getTable()->where('qid', $qid)->fetch();
        return $result ? ModelStoredQuery::createFromTableRow($result) : null;
    }

    /**
     * @param int|null $tagTypeId
     * @return Nette\Database\Table\Selection|null
     */
    public function findByTagType($tagTypeId) {
        if (!$tagTypeId) {
            return null;
        }
        $queryIds = $this->serviceStoredQueryTag->findByTagTypeId($tagTypeId)->fetchPairs('query_id', 'query_id');
        $result = $this->getTable()->where('query_id', $queryIds);
        return $result ?: null;
    }

}
