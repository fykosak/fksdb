<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\StoredQuery;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\StoredQuery\StoredQueryParameter;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int $query_id
 * @property-read string $qid
 * @property-read string $sql
 * @property-read string $name
 * @property-read string $description
 */
class QueryModel extends Model implements Resource
{

    public const RESOURCE_ID = 'storedQuery';

    /**
     * @return ParameterModel[]
     */
    public function getParameters(): array
    {
        $result = [];
        foreach ($this->getParameters2() as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getParameters2(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_STORED_QUERY_PARAM, 'query_id');
    }

    /**
     * @return StoredQueryParameter[]
     */
    public function getQueryParameters(): array
    {
        return array_map(fn(ParameterModel $model) => StoredQueryParameter::fromModel($model), $this->getParameters());
    }

    public function getTags(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_STORED_QUERY_TAG, 'query_id');
    }

    /**
     * @return TagTypeModel[]
     */
    public function getStoredQueryTagTypes(): array
    {
        $tags = $this->getTags();
        $result = [];
        /** @var TagModel $tag */
        foreach ($tags as $tag) {
            $result[] = $tag->tag_type;
        }
        return $result;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
