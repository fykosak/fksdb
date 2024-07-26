<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Spam dashboard'), 'fas fa-envelopes-bulk');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }
}
