<?php

namespace PublicModule;

/**
 * Just proof of concept.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    public function renderDefault() {
        $p = $this->getUser()->getIdentity()->getPerson();
        $cs = $p->getContestants()->fetch('ct_id');
        
    }

}
