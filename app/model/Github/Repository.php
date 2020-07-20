<?php

namespace Github;

use Nette\SmartObject;

/**
 * Class Repository
 * @author Michal Koutný <michal@fykos.cz>
 */
class Repository {

    use SmartObject;

    /** @var string $name */
    public $id;

    /** @var string $name */
    public $full_name;

    /** @var User $user */
    public $owner;

}
