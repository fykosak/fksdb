<?php

namespace Authorization\Assertions;

use DbNames;
use Exports\StoredQuery;
use Nette\Database\Connection;
use Nette\Object;
use Nette\Security\Permission;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractEventOrgAssertion extends Object {

    private $eventTypeId;
    private $parameterName;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * AbstractEventOrgAssertion constructor.
     * @param $eventTypeId
     * @param $parameterName
     * @param User $user
     * @param Connection $connection
     */
    function __construct($eventTypeId, $parameterName, User $user, Connection $connection) {
        if (!is_array($eventTypeId)) {
            $eventTypeId = [$eventTypeId];
        }
        $this->eventTypeId = $eventTypeId;
        $this->parameterName = $parameterName;
        $this->user = $user;
        $this->connection = $connection;
    }

    /**
     * @param Permission $acl
     * @param $role
     * @param $resourceId
     * @param $privilege
     * @param null $parameterValue
     * @return bool
     */
    public function __invoke(Permission $acl, $role, $resourceId, $privilege, $parameterValue = null) {
        $storedQuery = $acl->getQueriedResource();

        if (!$storedQuery instanceof StoredQuery) {
            //  throw new InvalidArgumentException('Expected StoredQuery, got \'' . get_class($storedQuery) . '\'.');
        }

        $identity = $this->user->getIdentity();
        $person = $identity ? $identity->getPerson() : null;
        if (!$person) {
            return false;
        }
        $rows = $this->connection->table(DbNames::TAB_EVENT_ORG)
            ->where('person_id', $person->person_id)
            ->where('event.event_type_id', $this->eventTypeId);

        // $queryParameters = $storedQuery->getParameters(true);
        if ($this->parameterName) {
            $rows->where('event.' . $this->parameterName, /*$queryParameters[$this->parameterName]*/
                $parameterValue);
        }
        return count($rows) > 0;
    }
}
