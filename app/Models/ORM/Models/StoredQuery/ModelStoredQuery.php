<?php

namespace FKSDB\Models\ORM\Models\StoredQuery;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\Resource;

/**
 * @todo Better (general) support for related collection setter.
 * @property-read int query_id
 * @property-read string qid
 * @property-read string sql
 * @property-read string name
 */
class ModelStoredQuery extends AbstractModel implements Resource {

    public const RESOURCE_ID = 'storedQuery';

    /**
     * @return ModelStoredQueryParameter[]
     */
    public function getParameters(): array {
        $result = [];
        foreach ($this->related(DbNames::TAB_STORED_QUERY_PARAM, 'query_id') as $row) {
            $result[] = ModelStoredQueryParameter::createFromActiveRow($row);
        }
        return $result;
    }

    public function getTags(): GroupedSelection {
        return $this->related(DbNames::TAB_STORED_QUERY_TAG, 'query_id');
    }

    /**
     * @return ModelStoredQueryTagType[]
     */
    public function getStoredQueryTagTypes(): array {
        $tags = $this->getTags();
        $result = [];
        foreach ($tags as $tag) {
            $result[] = ModelStoredQueryTag::createFromActiveRow($tag)->getTagType();
        }
        return $result;
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
