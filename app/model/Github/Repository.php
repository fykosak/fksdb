<?php

namespace Github;

use Nette\Object;

class Repository extends Object {

    /** @var string $name */
    public $id;

    /** @var string $name */
    public $full_name;

    /** @var User $user */
    public $owner;

}
