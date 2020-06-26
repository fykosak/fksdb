<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\UI\PageTitle;

/**
 * Class DispatchPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DispatchPresenter extends AuthenticatedPresenter {

    protected function createComponentContestBadge(): ContestBadge {
        return new ContestBadge($this->getContext());
    }

    protected function createComponentDispatchGrid(): DispatchGrid {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        return new DispatchGrid($login->getPerson(), $this->getContext());
    }

    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('List of events'), 'fa fa-calendar'));
    }

    protected function beforeRender() {
        $this->getPageStyleContainer()->styleId = 'event';
        $this->getPageStyleContainer()->navBarClassName = 'bg-dark navbar-dark';
        parent::beforeRender();
    }
}
