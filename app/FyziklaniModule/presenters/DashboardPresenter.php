<?php

namespace FyziklaniModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('FYKOSí Fyziklání'));
        $this->setIcon('fa fa-dashboard');
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani', 'dashboard'));
    }
}
