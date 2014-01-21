<?php

namespace PublicModule;

use AuthenticationPresenter;

/**
 * Just proof of concept.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    protected function unauthorizedAccess() {
        if ($this->getParam(AuthenticationPresenter::PARAM_DISPATCH)) {
            parent::unauthorizedAccess();
        } else {
            $this->redirect(':Authentication:login'); // ask for a central dispatch
        }
    }

    public function authorizedDefault() {
        $login = $this->getUser()->getIdentity();
        $access = (bool)$login;
        $this->setAuthorized($access);
    }

    public function titleDefault() {
        $this->setTitle(_('Pultík'));
    }

    public function renderDefault() {
        
    }

}
