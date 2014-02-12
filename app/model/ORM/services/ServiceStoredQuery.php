<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQuery extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY;
    protected $modelClassName = 'ModelStoredQuery';

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

}

