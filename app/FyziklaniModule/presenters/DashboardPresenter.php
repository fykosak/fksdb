<?php

namespace FyziklaniModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Fyziklani dashboard'));
        $this->setIcon('fa fa-dashboard');
    }

    public function authorizedDefault() {
        if (!$this->isEventFyziklani()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->eventIsAllowed('fyziklani', 'dashboard'));
    }
}
