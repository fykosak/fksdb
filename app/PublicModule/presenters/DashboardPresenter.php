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
        $access = $login ? $login->isContestant($this->yearCalculator) : false;
        $this->setAuthorized($access);
    }

    public function titleDefault() {
        $this->setTitle(_('Řešitelský pultík'));
    }

    public function renderDefault() {
        $contestId = $this->getContestant()->getContest()->contest_id;
        $key = $this->context->parameters['contestMapping'][$contestId];
        $url = $this->context->parameters['website'][$key];

        $this->template->website = $url;
    }

}
