<?php

namespace FyziklaniModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Herní systém Fyziklání'));
        $this->setIcon('fa fa-dashboard');
    }

    public function authorizedDefault() {
        if ($this->getEvent()->event_type_id !== 1) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->eventIsAllowed('fyziklani', 'dashboard'));
    }
}
