<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\ORM\ModelLogin;
use ServiceLogin;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueLoginFactory {

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    public function create(ModelLogin $login = null) {
        $rule = new UniqueLogin($this->serviceLogin);
        $rule->setIgnoredLogin($login);

        return $rule;
    }

}
