<?php

namespace FKSDB\Github;

use Nette\SmartObject;

/**
 * Class User
 * @author Michal Koutný <michal@fykos.cz>
 */
class User {
    use SmartObject;
    /** @var string $name */
    public $id;

    /** @var string $name */
    public $login;

}
