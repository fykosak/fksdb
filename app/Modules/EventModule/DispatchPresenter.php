<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Models\UI\PageTitle;

class DispatchPresenter extends AuthenticatedPresenter {

    protected function createComponentContestBadge(): ContestBadge {
        return new ContestBadge($this->getContext());
    }

    protected function createComponentDispatchGrid(): DispatchGrid {
        return new DispatchGrid($this->getContext());
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('List of events'), 'fa fa-calendar'));
    }

    protected function beforeRender(): void {
        $this->getPageStyleContainer()->styleId = 'event';
        $this->getPageStyleContainer()->setNavBarClassName('bg-dark navbar-dark');
        parent::beforeRender();
    }
}
