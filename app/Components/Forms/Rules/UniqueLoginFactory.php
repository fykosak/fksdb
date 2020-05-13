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

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * UniqueLoginFactory constructor.
     * @param ServiceLogin $serviceLogin
     */
    public function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    /**
     * @param ModelLogin|null $login
     * @return UniqueLogin
     */
    public function create(ModelLogin $login = null) {
        $rule = new UniqueLogin($this->serviceLogin);
        $rule->setIgnoredLogin($login);

        return $rule;
    }

}
