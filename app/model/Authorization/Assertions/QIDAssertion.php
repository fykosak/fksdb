<?php

namespace Authorization\Assertions;

use Exports\StoredQuery;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Security\Permission;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class QIDAssertion extends Object {

    private $qids;

    function __construct($qids) {
        if (!is_array($qids)) {
            $qids = array($qids);
        }
        $this->qids = $qids;
    }

    public function __invoke(Permission $acl, $role, $resourceId, $privilege) {
        $storedQuery = $acl->getQueriedResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got \'' . get_class($storedQuery) . '\'.');
        }
        $qid = isset($storedQuery->getQueryPattern()->qid) ? $storedQuery->getQueryPattern()->qid : null;

        return (bool)$qid && in_array($qid, $this->qids);
    }

}
