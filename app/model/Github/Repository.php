<?php

namespace Github;

use Nette\SmartObject;

/**
 * Class Repository
 * *
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
