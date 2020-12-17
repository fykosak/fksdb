<?php

namespace FKSDB\ORM\Models\StoredQuery;

use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyModel;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;

/**
 * @todo Better (general) support for related collection setter.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read string php_post_proc
 * @property-read int query_id
 * @property-read string qid
 * @property-read string sql
 * @property-read string name
 */
class ModelStoredQuery extends AbstractModelSingle implements IResource {
    use DeprecatedLazyModel;

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
