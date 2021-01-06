<?php

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\UI\PageTitle;

/**
 * Class DashboardPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {
    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Fyziklani game app'), 'fa fa-dashboard'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.dashboard', 'default'));
    }
}
