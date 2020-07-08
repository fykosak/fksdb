<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Events\EventNotFoundException;
use FKSDB\UI\PageTitle;

/**
 * Class DashboardPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {
    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('Fyziklani game app'), 'fa fa-dashboard'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.dashboard', 'default'));
    }
}
