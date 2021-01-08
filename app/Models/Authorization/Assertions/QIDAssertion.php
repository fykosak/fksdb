<?php

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\StoredQuery\StoredQuery;
use Nette\InvalidArgumentException;
use Nette\Security\IResource;
use Nette\Security\IRole;
use Nette\Security\Permission;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class QIDAssertion {

    use SmartObject;

    private array $qIds;

    /**
     * QIDAssertion constructor.
     * @param array|string $qids
     */
    public function __construct($qids) {
        if (!is_array($qids)) {
            $qids = [$qids];
        }
        $this->qIds = $qids;
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
        $qid = $storedQuery->getQId();
        return (bool)$qid && in_array($qid, $this->qIds);
    }

}
