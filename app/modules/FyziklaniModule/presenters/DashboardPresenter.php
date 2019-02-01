<?php

namespace FyziklaniModule;

class DashboardPresenter extends BasePresenter {
    /**
     * @return void
     */
    public function titleDefault() {
        $this->setTitle(_('Herní systém Fyziklání'));
        $this->setIcon('fa fa-dashboard');
    }

    /**
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function authorizedDefault() {
        if (!$this->isEventFyziklani()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->eventIsAllowed('fyziklani.dashboard', 'default'));
    }
}
