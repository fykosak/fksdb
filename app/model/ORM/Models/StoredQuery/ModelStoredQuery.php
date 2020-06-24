<?php

namespace FKSDB\ORM\Models\StoredQuery;

use Exports\StoredQueryPostProcessing;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use InvalidArgumentException;
use FKSDB\ORM\ModelsMulti\ModelMStoredQueryTag;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;

/**
 * @todo Better (general) support for related collection setter.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read string php_post_proc
 * @property-read int query_id
 * @property-read string qid
 */
class ModelStoredQuery extends AbstractModelSingle implements IResource {
    /**
     * @var array
     */
    private $outerParameters;

    /**
     * @var StoredQueryPostProcessing
     */
    private $postProcessing;

    const RESOURCE_ID = 'storedQuery';

    /**
     * @param bool $outer
     * @return ModelStoredQueryParameter[]
     */
    public function getParameters($outer = true): array {
        if ($this->outerParameters && $outer) {
            return $this->outerParameters;
        } else {
            if (!isset($this->query_id)) {
                $this->query_id = null;
            }
            $result = [];
            foreach ($this->related(DbNames::TAB_STORED_QUERY_PARAM, 'query_id') as $row) {
                $result[] = ModelStoredQueryParameter::createFromActiveRow($row);
            }
            return $result;
        }
    }

    /**
     * @param $value
     * @return void
     */
    public function setParameters($value) {
        $this->outerParameters = $value;
    }

    /**
     * @return StoredQueryPostProcessing|null
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

    public function getTags(): GroupedSelection {
        return $this->related(DbNames::TAB_STORED_QUERY_TAG, 'query_id');
    }

    /**
     * @return ModelMStoredQueryTag[]
     */
    public function getMStoredQueryTags(): array {
        $tags = $this->getTags();

        if (!$tags || count($tags) == 0) {
            return [];
        }
        $result = [];
        /** @var ModelStoredQueryTag $tag */
        foreach ($tags as $tag) {
            $tag->tag_type_id; // stupid touch
            $tagType = $tag->ref(DbNames::TAB_STORED_QUERY_TAG_TYPE, 'tag_type_id');
            $result[] = ModelMStoredQueryTag::createFromExistingModels(
                ModelStoredQueryTagType::createFromActiveRow($tagType), ModelStoredQueryTag::createFromActiveRow($tag)
            );
        }
        return $result;
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
