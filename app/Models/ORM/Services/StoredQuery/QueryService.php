<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;

/**
 * @phpstan-extends Service<QueryModel>
 */
final class QueryService extends Service
{
    public function findByQid(string $qid): ?QueryModel
    {
        /** @var QueryModel|null $query */
        $query = $this->getTable()->where('qid', $qid)->fetch();
        return $query;
    }

    /**
     * @phpstan-return TypedSelection<QueryModel>
     * @param int[] $tagTypeIds
     */
    public function findByTagType(array $tagTypeIds): TypedSelection
    {
        return $this->getTable()->where(':stored_query_tag.tag_type_id', $tagTypeIds);
    }
}
