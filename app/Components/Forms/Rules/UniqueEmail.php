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
class UniqueEmail {

    const CHECK_LOGIN = 0x1;
    const CHECK_PERSON = 0x2;

    /**
     * @var int  Logic sum of CHECK_* flags.
     */
    private $mode;

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ModelLogin
     */
    private $ignoredLogin;

    /**
     * @var ModelPerson
     */
    private $ignoredPerson;

    function __construct($mode, ServiceLogin $serviceLogin, ServicePersonInfo $servicePersonInfo) {
        $this->mode = $mode;
        $this->serviceLogin = $serviceLogin;
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function getIgnoredLogin() {
        return $this->ignoredLogin;
    }

    public function setIgnoredLogin(ModelLogin $ignoredLogin = null) {
        $this->ignoredLogin = $ignoredLogin;
    }

    public function getIgnoredPerson() {
        return $this->ignoredPerson;
    }

    public function setIgnoredPerson(ModelPerson $ignoredPerson = null) {
        $this->ignoredPerson = $ignoredPerson;
    }

    public function __invoke(BaseControl $control) {
        $email = $control->getValue();

        if ($this->mode & self::CHECK_LOGIN) {
            $conflicts = $this->serviceLogin->getTable()->where(array('email' => $email));
            if ($this->ignoredLogin && $this->ignoredLogin->login_id) {
                $conflicts->where('NOT login_id = ?', $this->ignoredLogin->login_id);
            }
            if (count($conflicts) > 0) {
                return false;
            }
        }

        if ($this->mode & self::CHECK_PERSON) {
            $conflicts = $this->servicePersonInfo->getTable()->where(array('email' => $email));
            if ($this->ignoredPerson && $this->ignoredPerson->person_id) {
                $conflicts->where('NOT person_id = ?', $this->ignoredPerson->person_id);
            }
            if (count($conflicts) > 0) {
                return false;
            }
        }

        return true;
    }

}
