<?php

namespace Authorization\Assertions;

use Nette\Database\Context;
use Nette\Security\IUserStorage;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventOrgByYearAssertion extends AbstractEventOrgAssertion {
    /**
     * EventOrgByYearAssertion constructor.
     * @param $eventTypeId
     * @param IUserStorage $user
     * @param Context $connection
     */
    public function __construct($eventTypeId, IUserStorage $user, Context $connection) {
        parent::__construct($eventTypeId, 'year', $user, $connection);
    }
}
