<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fas fa-user');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }
}
