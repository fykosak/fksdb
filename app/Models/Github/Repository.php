<?php

namespace FKSDB\Models\Github;

use Nette\SmartObject;

class Repository
{
    use SmartObject;

    public string $id;

    public string $full_name;

    public User $owner;
}
