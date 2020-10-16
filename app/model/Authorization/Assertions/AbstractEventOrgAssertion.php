<?php

namespace FKSDB\Authorization\Assertions;

use FKSDB\StoredQuery\StoredQuery;
use FKSDB\ORM\DbNames;
use Nette\Database\Context;
use Nette\Security\IResource;
use Nette\Security\IRole;
use Nette\Security\IUserStorage;
use Nette\Security\Permission;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @deprecated
 */
abstract class AbstractEventOrgAssertion {

    use SmartObject;

    private string $parameterName;

    private IUserStorage $user;

    private Context $connection;

    public function __construct(string $parameterName, IUserStorage $user, Context $connection) {
        $this->parameterName = $parameterName;
        $this->user = $user;
        $this->connection = $connection;
    }

    /**
     * @param Permission $acl
     * @param IRole $role
     * @param IResource|string|null $resourceId
     * @param string $privilege
     * @param null $parameterValue
     * @return bool
     */
    public function __invoke(Permission $acl, $role, $resourceId, $privilege, $parameterValue = null): bool {
        $storedQuery = $acl->getQueriedResource();

        if (!$storedQuery instanceof StoredQuery) {
            //  throw new InvalidArgumentException('Expected StoredQuery, got "' . get_class($storedQuery) . '".');
        }

        $identity = $this->user->getIdentity();
        $person = $identity ? $identity->getPerson() : null;
        if (!$person) {
            return false;
        }
        $rows = $this->connection->table(DbNames::TAB_EVENT_ORG)
            ->where('person_id', $person->person_id);

        if ($this->parameterName) {
            $rows->where('event.' . $this->parameterName, /*$queryParameters[$this->parameterName]*/
                $parameterValue);
        }
        return count($rows) > 0;
    }
}
