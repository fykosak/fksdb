<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{
    /**
     * @throws NoContestAvailable
     */
    public function authorizedDefault(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromOwnResource($this->getSelectedContest()),
            'organizerDashboard',
            $this->getSelectedContest()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Organizer\'s dashboard'), 'fas fa-chalkboard');
    }
}
