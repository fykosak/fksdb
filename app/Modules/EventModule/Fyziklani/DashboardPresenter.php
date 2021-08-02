<?php

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    public function titleDefault(): void
    {
        $this->setPageTitle(new PageTitle(_('Fyziklani game app'), 'fas fa-laptop-code'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.dashboard', 'default'));
    }
}
