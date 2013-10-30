<?php

namespace FKSDB\Components\Forms\Rules;

use ModelLogin;
use ModelPerson;
use ServiceLogin;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueEmailFactory {

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    function __construct(ServiceLogin $serviceLogin, ServicePersonInfo $servicePersonInfo) {
        $this->serviceLogin = $serviceLogin;
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function create(ModelPerson $person = null, ModelLogin $login = null) {
        $mode = UniqueEmail::CHECK_LOGIN | UniqueEmail::CHECK_PERSON;
        $rule = new UniqueEmail($mode, $this->serviceLogin, $this->servicePersonInfo);
        $login = $login ? : ($person ? $person->getLogin() : null);
        $rule->setIgnoredPerson($person);
        $rule->setIgnoredLogin($login);

        return $rule;
    }

}
