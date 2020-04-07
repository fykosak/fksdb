<?php

namespace CommonModule;

/**
 * Class DashboardPresenter
 * @package CoreModule
 */
class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Common dashboard'),'fa fa-dashboard');
    }
}
