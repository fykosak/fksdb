<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 */

namespace FKSDB\Components\Controls\Nav;

use Nette\Application\UI\Control;
use ModelContest;
use Nette\Http\Session;
use ServiceContest;

abstract class Nav extends Control {

    const SESSION_PREFIX = 'contestPreset';

    /**
     * @var bool
     */
    protected $valid = false;
    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var string
     */
    protected $role = null;

    /**
     * @param $role string
     */
    public function setRole($role) {
        $this->role = $role;
    }


    /**
     * @var ServiceContest
     */
    protected $serviceContest;

    /**
     * @var ModelContest
     */
    protected $contest;
    /**
     * @var Session
     */
    protected $session;

    /**
     * @return boolean
     */
    abstract function isValid();

    /**
     * @return integer
     */
    abstract function syncRedirect();


    /**
     * @return \ModelLogin
     */
    protected function getLogin() {
        return $this->getPresenter()->getUser()->getIdentity();
    }

}
