<?php

namespace Authorization\Assertions;

use Exports\StoredQuery;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Security\Permission;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagAssertion extends Object {

    private $tagNames;

    function __construct($tagNames) {
        if (!is_array($tagNames)) {
            $tagNames = array($tagNames);
        }
        $this->tagNames = $tagNames;
    }

    public function __invoke(Permission $acl, $role, $resourceId, $privilege) {
        $storedQuery = $acl->getQueriedResource();
        if (!$storedQuery instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got \'' . get_class($storedQuery) . '\'.');
        }
        foreach($storedQuery->getQueryPattern()->getMStoredQueryTags() as $modelMStoredQueryTag) {
            $tagName = $modelMStoredQueryTag->getStoredQueryTagType()->name;
            if (in_array($tagName, $this->tagNames)) {
                return true;
            }
        }
        return false;
    }

}