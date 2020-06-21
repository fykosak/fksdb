<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\UI\PageTitle;

/**
 * Class DashboardPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('Common dashboard'), 'fa fa-dashboard'));
    }
}
