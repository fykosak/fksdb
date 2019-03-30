<?php

namespace Authorization\Assertions;

use Nette\Database\Connection;
use Nette\Security\User;
/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventOrgByIdAssertion extends AbstractEventOrgAssertion {

    /**
     * EventOrgByIdAssertion constructor.
     * @param $eventTypeId
     * @param User $user
     * @param Connection $connection
     */
    public function __construct($eventTypeId, User $user, Connection $connection) {
        parent::__construct($eventTypeId, 'event_id', $user, $connection);

    }
}
