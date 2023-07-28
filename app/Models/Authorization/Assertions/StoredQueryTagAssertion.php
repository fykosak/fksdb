<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\StoredQuery\StoredQuery;
use Nette\Security\Permission;
use Nette\SmartObject;

// TODO isnt used anymore
class StoredQueryTagAssertion implements Assertion
{
    use SmartObject;

    private array $tagNames;

    public function __construct(array $tagNames)
    {
        $this->tagNames = $tagNames;
    }

    /**
     * @throws BadTypeException
     */
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $storedQuery = $acl->getQueriedResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new BadTypeException(StoredQuery::class, $storedQuery);
        }
        foreach ($storedQuery->queryPattern->getStoredQueryTagTypes() as $tagType) {
            if (in_array($tagType->name, $this->tagNames)) {
                return true;
            }
        }
        return false;
    }
}
