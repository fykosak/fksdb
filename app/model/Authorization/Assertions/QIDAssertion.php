<?php

namespace Authorization\Assertions;

use Exports\StoredQuery;
use Nette\InvalidArgumentException;
use Nette\Security\Permission;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class QIDAssertion {

    use SmartObject;

    /**
     * @var array
     */
    private $qids;

    /**
     * QIDAssertion constructor.
     * @param $qids
     */
    public function __construct($qids) {
        if (!is_array($qids)) {
            $qids = [$qids];
        }
        $this->qids = $qids;
    }

    /**
     * @param Permission $acl
     * @param $role
     * @param $resourceId
     * @param $privilege
     * @return bool
     */
    public function __invoke(Permission $acl, $role, $resourceId, $privilege) {
        $storedQuery = $acl->getQueriedResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got \'' . get_class($storedQuery) . '\'.');
        }
        $qid = isset($storedQuery->getModelQuery()->qid) ? $storedQuery->getModelQuery()->qid : null;

        return (bool)$qid && in_array($qid, $this->qids);
    }

}
