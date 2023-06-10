<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use Fykosak\Utils\UI\PageTitle;

class DispatchPresenter extends AuthenticatedPresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('List of events'), 'fas fa-calendar-alt');
    }

    public function authorizedDefault(): bool
    {
        return true;
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
        $this->getPageStyleContainer()->styleIds[] = 'event';
        $this->getPageStyleContainer()->navBarClassName = 'bg-dark navbar-dark';
        $this->getPageStyleContainer()->navBrandPath = '/images/logo/white.svg';
        parent::beforeRender();
    }
}
