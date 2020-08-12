<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueLoginFactory {

    private ServiceLogin $serviceLogin;

    /**
     * UniqueLoginFactory constructor.
     * @param ServiceLogin $serviceLogin
     */
    public function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    public function create(?ModelLogin $login): UniqueLogin {
        $rule = new UniqueLogin($this->serviceLogin);
        $rule->setIgnoredLogin($login);
        return $rule;
    }
}
