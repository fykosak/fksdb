<?php

namespace Authorization\Assertions;

use FKSDB\ORM\Models\ModelPerson;
use Nette\Database\Connection;
use Nette\Security\IUserStorage;
use Nette\Security\Permission;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventOrgByIdAssertion extends AbstractEventOrgAssertion {

    /**
     * EventOrgByIdAssertion constructor.
     * @param $eventTypeId
     * @param IUserStorage $user
     * @param Connection $connection
     */
    public function __construct($eventTypeId, IUserStorage $user, Connection $connection) {
        parent::__construct($eventTypeId, 'event_id', $user, $connection);
    }
}
