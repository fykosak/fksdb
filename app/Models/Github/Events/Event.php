<?php

declare(strict_types=1);

namespace FKSDB\Models\Github\Events;

use FKSDB\Models\Github\Repository;
use FKSDB\Models\Github\User;
use Nette\SmartObject;

abstract class Event
{
    use SmartObject;

    public const HTTP_HEADER = 'X-GitHub-Event';

    public Repository $repository;

    public User $sender;
}
