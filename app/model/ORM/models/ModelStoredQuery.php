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
    
    public function getTags() {
        if (!isset($this->query_id)) {
            $this->query_id = null;
        }
        return $this->related(DbNames::TAB_STORED_QUERY_TAG, 'query_id');
    }
    
    /**
     * @return ModelMStoredQueryTag[]
     */
    public function getMStoredQueryTags() {
        $tags = $this->getTags();
        
        if (!$tags || count($tags) == 0) {
            return array();
        }
        
        $result = array();
        foreach ($tags as $tag) {
            $tag->tag_type_id; // stupid touch
            $tagType = $tag->ref(DbNames::TAB_STORED_QUERY_TAG_TYPE, 'tag_type_id');
            $result[] = ModelMStoredQueryTag::createFromExistingModels(
                ModelStoredQueryTagType::createFromTableRow($tagType), ModelStoredQueryTag::createFromTableRow($tag)
            );
        }
        return $result;
    }

    public function getResourceId() {
        return 'storedQuery';
    }

}
