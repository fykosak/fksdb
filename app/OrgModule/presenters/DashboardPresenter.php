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
        $login = $this->getUser()->getIdentity();
        $access = $login ? $login->isOrg($this->yearCalculator) : false;
        $this->setAuthorized($access);
    }

    public function titleDefault() {
        $this->setTitle(_('Organizátorský pultík'));
    }

    public function renderDefault() {
        
    }

}
