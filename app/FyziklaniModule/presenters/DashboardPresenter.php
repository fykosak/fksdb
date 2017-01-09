<?php

namespace FyziklaniModule;


class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Fykosí Fyzikláni'));
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->getEventAuthorizator()->isAllowed('fyziklani', 'dashboard', $this->getCurrentEvent()));
    }
}
