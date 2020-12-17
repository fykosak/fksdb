<?php

namespace FKSDB\Model\Github;

use Nette\SmartObject;

/**
 * Class Repository
 * @author Michal Koutný <michal@fykos.cz>
 */
class Repository {

    use SmartObject;

    public string $id;

    public string $full_name;

    public User $owner;
}
