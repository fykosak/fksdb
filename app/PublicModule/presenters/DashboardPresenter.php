<?php

namespace PublicModule;

/**
 * Just proof of concept.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    public function actionDefault() {
        if (!$this->user->getIdentity()->isContestant($this->yearCalculator)) {
            $this->redirect(':Authentication:login');
        }
    }

}
