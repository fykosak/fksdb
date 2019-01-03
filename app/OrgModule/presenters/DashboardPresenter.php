<?php

namespace OrgModule;

/**
 * Homepage presenter.
 */
class DashboardPresenter extends BasePresenter {

    public function authorizedDefault() {
        $login = $this->getUser()->getIdentity();
        $access = $login ? $login->isOrg($this->yearCalculator) : false;
        $this->setAuthorized($access);
    }

    public function titleDefault() {
        $this->setTitle(_('Organizátorský pultík'));
        $this->setIcon('fa fa-dashboard');
    }
}
