<?php

namespace EventModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(\sprintf(_('Event %s'), $this->getEvent()->name));
        $this->setIcon('fa fa-dashboard');
    }

    public function authorizedDefault() {
        //$this->setAuthorized($this->eventIsAllowed('event', 'dashboard'));
    }
}
