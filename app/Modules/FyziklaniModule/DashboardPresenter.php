<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Model\Events\Exceptions\EventNotFoundException;
use FKSDB\Model\UI\PageTitle;

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
