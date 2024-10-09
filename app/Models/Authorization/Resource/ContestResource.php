<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Security\Resource;

interface ContestResource extends Resource
{
    public function getContest(): ContestModel;
}
