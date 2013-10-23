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

    public function renderDefault() {
        $contestId = $this->getContestant()->getContest()->contest_id;
        $key = $this->context->parameters['contestMapping'][$contestId];
        $url = $this->context->parameters['website'][$key];

        $this->template->website = $url;
    }

}
