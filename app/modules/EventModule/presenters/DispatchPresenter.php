<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\UI\PageStyleContainer;

/**
 * Class DispatchPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DispatchPresenter extends AuthenticatedPresenter {

    public function createComponentContestBadge(): ContestBadge {
        return new ContestBadge($this->getContext());
    }

    public function createComponentDispatchGrid(): DispatchGrid {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        return new DispatchGrid($login->getPerson(), $this->getContext());
    }

    public function titleDefault() {
        $this->setTitle(_('List of events'), 'fa fa-calendar');
    }

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        $container->styleId = 'event';
        $container->navBarClassName = 'bg-dark navbar-dark';
        return $container;
    }
}
