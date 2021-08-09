<?php

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\StoredQuery\StoredQuery;
use Nette\InvalidArgumentException;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\Role;
use Nette\SmartObject;

class QIDAssertion
{

    use SmartObject;

    private array $qIds;

    /**
     * QIDAssertion constructor.
     * @param array|string $qids
     */
    public function __construct($qids)
    {
        if (!is_array($qids)) {
            $qids = [$qids];
        }
        $this->qIds = $qids;
    }

    /**
     * @param Permission $acl
     * @param Role $role
     * @param Resource|string|null $resourceId
     * @param string|null $privilege
     * @return bool
     */
    public function __invoke(Permission $acl, $role, $resourceId, $privilege): bool
    {
        $storedQuery = $acl->getQueriedResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got \'' . get_class($storedQuery) . '\'.');
        }
        $qid = $storedQuery->getQId();
        return $qid && in_array($qid, $this->qIds);
    }
}
