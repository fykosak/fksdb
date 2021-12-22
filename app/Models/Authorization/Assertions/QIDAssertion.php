<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\StoredQuery\StoredQuery;
use Nette\InvalidArgumentException;
use Nette\Security\Permission;
use Nette\SmartObject;

// TODO isnt used anymore
class QIDAssertion implements Assertion
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

    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $storedQuery = $acl->getQueriedResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got \'' . get_class($storedQuery) . '\'.');
        }
        $qid = $storedQuery->getQId();
        return $qid && in_array($qid, $this->qIds);
    }
}
