<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;

class ServiceStoredQuery extends Service
{

    public function findByQid(string $qid): ?ModelStoredQuery
    {
        /** @var ModelStoredQuery $result */
        $result = $this->getTable()->where('qid', $qid)->fetch();
        return $result;
    }

    public function findByTagType(array $tagTypeIds): ?TypedSelection
    {
        return $this->getTable()->where(':stored_query_tag.tag_type_id', $tagTypeIds);
    }
}
