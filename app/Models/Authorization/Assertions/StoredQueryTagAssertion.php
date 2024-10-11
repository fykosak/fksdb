<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\StoredQuery\StoredQuery;
use Nette\Security\Permission;
use Nette\SmartObject;

class StoredQueryTagAssertion implements Assertion
{
    use SmartObject;

    /**
     * @phpstan-var string[]
     */
    private array $tagNames;

    /**
     * @phpstan-param string[] $tagNames
     */
    public function __construct(array $tagNames)
    {
        $this->tagNames = $tagNames;
    }

    /**
     * @throws WrongAssertionException
     */
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        $storedQuery = $holder->getResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new WrongAssertionException();
        }
        foreach ($storedQuery->queryPattern->getStoredQueryTagTypes() as $tagType) {
            if (in_array($tagType->name, $this->tagNames)) {
                return true;
            }
        }
        return false;
    }
}
