<?php

namespace FKSDB\Github\Events;

use FKSDB\Github\Repository;
use FKSDB\Github\User;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class Event {
    use SmartObject;

    public const HTTP_HEADER = 'X-GitHub-Event';

    public Repository $repository;

    public User $sender;

}
