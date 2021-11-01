<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Warehouse'), 'fa fa-warehouse');
    }
}
