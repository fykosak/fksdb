<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 */

namespace FKSDB\Components\Controls\Nav;

use Nette\Application\UI\Control;

abstract class Nav extends Control {

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
     * @return boolean
     */
    abstract function isValid();


    /**
     * @return \ModelLogin
     */
    protected function getLogin() {
        return $this->getPresenter()->getUser()->getIdentity();
    }

}
