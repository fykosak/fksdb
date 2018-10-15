<?php

namespace FyziklaniModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Fyziklani'));
        $this->setIcon('fa fa-dashboard');
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani', 'dashboard'));
    }
}
