<?php

namespace FKSDB\Models\Github;

use Nette\SmartObject;

/**
 * Class User
 * @author Michal Koutný <michal@fykos.cz>
 */
class User {
    use SmartObject;

    public string $id;

    public string $login;
}
