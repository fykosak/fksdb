<?php

use FKSDB\ORM\ModelStoredQuery;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQuery extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY;
    protected $modelClassName = 'FKSDB\ORM\ModelStoredQuery';

    /**
     *
     * @var ServiceStoredQueryTag
     */
    private $serviceStoredQueryTag;

    public function __construct(\Nette\Database\Connection $connection, ServiceStoredQueryTag $serviceStoredQueryTag) {
        parent::__construct($connection);
        $this->serviceStoredQueryTag = $serviceStoredQueryTag;
    }

    /**
     * Syntactic sugar.
     *
     * @param string|null $qid
     * @return ModelStoredQuery|null
     */
    public function findByQid($qid) {
        if (!$qid) {
            return null;
        }
        $result = $this->getTable()->where('qid', $qid)->fetch();
        return $result ? : null;
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
        return $result ? : null;
    }

}

