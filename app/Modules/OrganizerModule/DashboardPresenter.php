<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{
    /**
     * @throws NoContestAvailable
     */
    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            $this->getSelectedContest(),
            'organizerDashboard',
            $this->getSelectedContest()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Organizer\'s dashboard'), 'fas fa-chalkboard');
    }
}
