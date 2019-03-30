<?php

namespace FKSDB\ORM\Models\StoredQuery;

use Exports\StoredQueryPostProcessing;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use InvalidArgumentException;
use ModelMStoredQueryTag;
use Nette\Security\IResource;

/**
 * @todo Better (general) support for related collection setter.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string php_post_proc
 */
class ModelStoredQuery extends AbstractModelSingle implements IResource {

    private $outerParameters;

    /**
     * @var StoredQueryPostProcessing
     */
    private $postProcessing;

    /**
     * @param bool $outer
     * @return array
     */
    public function getParameters($outer = true) {
        if ($this->outerParameters && $outer) {
            return $this->outerParameters;
        } else {
            if (!isset($this->query_id)) {
                $this->query_id = null;
            }
            $result = [];
            foreach ($this->related(DbNames::TAB_STORED_QUERY_PARAM, 'query_id') as $row) {
                $result[] = ModelStoredQueryParameter::createFromTableRow($row);
            }
            return $result;
        }
    }

    /**
     * @param $value
     */
    public function setParameters($value) {
        $this->outerParameters = $value;
    }

    /**
     * @return StoredQueryPostProcessing
     */
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

    /**
     * @return \Nette\Database\Table\GroupedSelection
     */
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
            return [];
        }
        $result = [];
        foreach ($tags as $tag) {
            $tag->tag_type_id; // stupid touch
            $tagType = $tag->ref(DbNames::TAB_STORED_QUERY_TAG_TYPE, 'tag_type_id');
            $result[] = ModelMStoredQueryTag::createFromExistingModels(
                ModelStoredQueryTagType::createFromTableRow($tagType), ModelStoredQueryTag::createFromTableRow($tag)
            );
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'storedQuery';
    }

}
