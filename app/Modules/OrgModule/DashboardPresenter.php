<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{
    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Organizer\'s dashboard'), 'fas fa-chalkboard');
    }
}
