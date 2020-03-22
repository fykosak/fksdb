<?php

namespace FyziklaniModule;

use Nette\Application\AbortException;
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
        $this->setTitle(_('Herní systém Fyziklání'));
        $this->setIcon('fa fa-dashboard');
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedDefault() {
        return $this->setAuthorized($this->isAllowedForEventOrg('fyziklani.dashboard', 'default'));
    }
}
