<?php

namespace FKSDB\Components\Forms\Rules;

use ModelLogin;
use ModelPerson;
use Nette\Forms\Controls\BaseControl;
use ServiceLogin;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueLogin {

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var ModelLogin
     */
    private $ignoredLogin;

    function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    public function getIgnoredLogin() {
        return $this->ignoredLogin;
    }

    public function setIgnoredLogin(ModelLogin $ignoredLogin = null) {
        $this->ignoredLogin = $ignoredLogin;
    }

    public function __invoke(BaseControl $control) {
        $login = $control->getValue();

        if (!$login) {
            return true;
        }

        $conflicts = $this->serviceLogin->getTable()->where(array('login' => $login));
        if ($this->ignoredLogin) {
            $conflicts->where('NOT login_id = ?', $this->ignoredLogin->login_id);
        }
        if (count($conflicts) > 0) {
            return false;
        }

        return true;
    }

}
