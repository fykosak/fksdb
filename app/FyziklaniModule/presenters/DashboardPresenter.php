<?php

namespace FyziklaniModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('FYKOSí Fyziklání'));
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->getEventAuthorizator()->isAllowed('fyziklani', 'dashboard', $this->getCurrentEvent()));
    }
}
