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
 * @property-read string name
 * @property-read string|null qid
 * @property-read string sql
 */
class ModelStoredQuery extends AbstractModelSingle implements IResource {

    const RESOURCE_ID = 'storedQuery';
    /**
     * @var array
     */
    private $outerParameters;

    /**
     * @var StoredQueryPostProcessing|null
     */
    private $postProcessing;

    public function getParameters(): GroupedSelection {
        return $this->related(DbNames::TAB_STORED_QUERY_PARAM, 'query_id');
    }

    /**
     * @return ModelStoredQueryParameter[]
     */
    public function getParametersAsArray(): array {
        if ($this->outerParameters) {
            return $this->outerParameters;
        } else {
            $result = [];
            foreach ($this->getParameters() as $row) {
                $result[] = ModelStoredQueryParameter::createFromActiveRow($row);
            }
            return $result;
        }
    }

    /**
     * @param mixed $value
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

    public function getFQName(): string {
        if ($this->qid) {
            return $this->name . ' (' . $this->qid . ')';
        }
        return $this->name;
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
