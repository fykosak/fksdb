<?php

use Nette\Security\IResource;
use Exports\StoredQueryPostProcessing;

/**
 * @todo Better (general) support for related collection setter.
 * 
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelStoredQuery extends AbstractModelSingle implements IResource {

    private $outerParameters;

    /**
     * @var StoredQueryPostProcessing
     */
    private $postProcessing;

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

    public function getPostProcessing() {
        if ($this->postProcessing == null && $this->php_post_proc) {
            $className = $this->php_post_proc;
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Expected class name, got '$className'.");
            }
            $this->postProcessing = new $className();
        }
        return $this->postProcessing;
    }

    public function getResourceId() {
        return 'storedQuery';
    }

}
