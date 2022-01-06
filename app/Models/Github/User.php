<?php

declare(strict_types=1);

namespace FKSDB\Models\Github;

use Nette\SmartObject;

class User {
    use SmartObject;

    public string $id;

    public string $login;
}
