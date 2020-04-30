<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\UI\PageStyleContainer;

/**
 * Class DispatchPresenter
 * @package EventModule
 */
class DispatchPresenter extends AuthenticatedPresenter {

    /**
     * @var ServiceEvent
     */
    protected $serviceEvent;

    /**
     * @param ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @return ContestBadge
     */
    public function createComponentContestBadge(): ContestBadge {
        return new ContestBadge();
    }

    /**
     * @return DispatchGrid
     */
    public function createComponentDispatchGrid(): DispatchGrid {
        /**
         * @var ModelPerson $person
         */
        $person = $this->user->getIdentity()->getPerson();
        return new DispatchGrid($person, $this->getContext());
    }

    public function titleDefault() {
        $this->setTitle(_('List of events'),'fa fa-calendar');
    }


    /**
     * @return PageStyleContainer
     */
    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        $container->styleId = 'event';
        $container->navBarClassName = 'bg-dark navbar-dark';
        return $container;
    }
}
