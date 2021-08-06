<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\Models\UI\PageTitle;
use FKSDB\Modules\Core\AuthenticatedPresenter;

class DispatchPresenter extends AuthenticatedPresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('List of events'), 'fa fa-calendar-alt');
    }

    protected function createComponentContestBadge(): ContestBadge
    {
        return new ContestBadge($this->getContext());
    }

    protected function createComponentDispatchGrid(): DispatchGrid
    {
        return new DispatchGrid($this->getContext());
    }

    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->styleId = 'event';
        $this->getPageStyleContainer()->setNavBarClassName('bg-dark navbar-dark');
        parent::beforeRender();
    }
}
