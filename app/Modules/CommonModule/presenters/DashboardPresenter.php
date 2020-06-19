<?php

namespace FKSDB\Modules\CommonModule;

/**
 * Class DashboardPresenter
 * *
 */
class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Common dashboard'),'fa fa-dashboard');
    }
}
