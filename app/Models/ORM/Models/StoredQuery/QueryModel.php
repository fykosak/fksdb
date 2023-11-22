<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\StoredQuery;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\StoredQuery\StoredQueryParameter;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int $query_id
 * @property-read string|null $qid
 * @property-read string $name
 * @property-read string|null $description
 * @property-read string $sql
 */
final class QueryModel extends Model implements Resource
{

    public const RESOURCE_ID = 'storedQuery';

    /**
     * @phpstan-return ParameterModel[]
     */
    public function getParameters(): array
    {
        $result = [];
        /** @var ParameterModel $row */
        foreach ($this->getParameters2() as $row) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ParameterModel>
     */
    public function getParameters2(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<ParameterModel> $selection */
        $selection = $this->related(DbNames::TAB_STORED_QUERY_PARAM, 'query_id');
        return $selection;
    }

    /**
     * @phpstan-return StoredQueryParameter[]
     */
    public function getQueryParameters(): array
    {
        return array_map(fn(ParameterModel $model) => StoredQueryParameter::fromModel($model), $this->getParameters());
    }

    /**
     * @phpstan-return TypedGroupedSelection<TagModel>
     */
    public function getTags(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TagModel> $selection */
        $selection = $this->related(DbNames::TAB_STORED_QUERY_TAG, 'query_id');
        return $selection;
    }

    /**
     * @phpstan-return TagTypeModel[]
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
