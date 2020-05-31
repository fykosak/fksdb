<?php

namespace CommonModule;

/**
 * Class DashboardPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    public function titleDefault(): void {
        $this->setTitle(_('Common dashboard'), 'fa fa-dashboard');
    }
}
