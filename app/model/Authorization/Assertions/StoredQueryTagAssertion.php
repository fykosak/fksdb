<?php

namespace FKSDB\Authorization\Assertions;

use FKSDB\StoredQuery\StoredQuery;
use Nette\InvalidArgumentException;
use Nette\Security\IResource;
use Nette\Security\IRole;
use Nette\Security\Permission;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagAssertion {

    use SmartObject;

    private array $tagNames;

    /**
     * StoredQueryTagAssertion constructor.
     * @param array|string $tagNames
     */
    public function __construct($tagNames) {
        if (!is_array($tagNames)) {
            $tagNames = [$tagNames];
        }
        $this->tagNames = $tagNames;
    }

    /**
     * @param Permission $acl
     * @param IRole $role
     * @param IResource|string|null $resourceId
     * @param string|null $privilege
     * @return bool
     */
    public function __invoke(Permission $acl, $role, $resourceId, $privilege): bool {
        $storedQuery = $acl->getQueriedResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got \'' . get_class($storedQuery) . '\'.');
        }
        foreach ($storedQuery->getQueryPattern()->getStoredQueryTagTypes() as $tagType) {
            if (in_array($tagType->name, $this->tagNames)) {
                return true;
            }
        }
        return false;
    }

}
