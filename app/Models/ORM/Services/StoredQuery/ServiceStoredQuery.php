<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\TypedTableSelection;

class ServiceStoredQuery extends AbstractService
{

    public function findByQid(string $qid): ?ModelStoredQuery
    {
        /** @var ModelStoredQuery $result */
        $result = $this->getTable()->where('qid', $qid)->fetch();
        return $result;
    }

    public function findByTagType(array $tagTypeIds): ?TypedTableSelection
    {
        return $this->getTable()->where(':stored_query_tag.tag_type_id', $tagTypeIds);
    }
}
