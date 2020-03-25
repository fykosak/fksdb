<?php

namespace FyziklaniModule;

use Nette\Application\BadRequestException;

/**
 * Class DashboardPresenter
 * @package FyziklaniModule
 */
class DashboardPresenter extends BasePresenter {
    /**
     * @return void
     */
    public function titleDefault() {
        $this->setTitle(_('Fyziklani game app'), 'fa fa-dashboard');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        return $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.dashboard', 'default'));
    }
}
