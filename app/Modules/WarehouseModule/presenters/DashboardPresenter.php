<?php

namespace FKSDB\Modules\WarehouseModule;

use FKSDB\UI\PageTitle;

/**
 * Class DashboardPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    protected function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Warehouse'), 'fa fa-truck'));
    }
}