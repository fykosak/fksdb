<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;

/**
 * Class DashboardPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('Fyziklani game app'), 'fa fa-dashboard'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.dashboard', 'default'));
    }
}
