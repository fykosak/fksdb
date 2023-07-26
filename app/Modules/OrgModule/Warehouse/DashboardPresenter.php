<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Warehouse'), 'fas fa-warehouse');
    }
}
