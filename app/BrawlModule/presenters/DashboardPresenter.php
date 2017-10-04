<?php

namespace BrawlModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Physics Brawl'));
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->eventIsAllowed('brawl', 'dashboard'));
    }
}
