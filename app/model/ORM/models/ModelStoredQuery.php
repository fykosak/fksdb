<?php

use Nette\Security\IResource;

/**
 * @todo Better (general) support for related collection setter.
 * 
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelStoredQuery extends AbstractModelSingle implements IResource {

    private $outerParameters;

    public function getParameters($outer = true) {
        if ($this->outerParameters && $outer) {
            return $this->outerParameters;
        } else {
            if (!isset($this->query_id)) {
                $this->query_id = null;
            }
            $result = array();
            foreach ($this->related(DbNames::TAB_STORED_QUERY_PARAM, 'query_id') as $row) {
                $result[] = ModelStoredQueryParameter::createFromTableRow($row);
            }
            return $result;
        }
    }

    public function setParameters($value) {
        $this->outerParameters = $value;
    }

    public function getResourceId() {
        return 'query.stored';
    }

}
