<?php

namespace OrgModule;

use ServiceSubmit;

/**
 * Homepage presenter.
 */
class DashboardPresenter extends BasePresenter {

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    public function authorizedDefault() {
        /**
         * @var $login \ModelLogin
         */
        $login = $this->getUser()->getIdentity();
        $access = $login ? $login->isOrg($this->yearCalculator) : false;
        $this->setAuthorized($access);
    }

    public function titleDefault() {
        $this->setIcon('<i class="fa fa-tachometer" aria-hidden="true"></i>');
        $this->setTitle(_('Organizátorský pultík'));
    }

    public function renderDefault() {

    }

}
