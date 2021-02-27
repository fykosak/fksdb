<?php

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Models\UI\PageTitle;

/**
 * Class DashboardPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Warehouse'), 'fa fa-warehouse'));
    }
}
