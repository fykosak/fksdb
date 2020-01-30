<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

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
     * @return LanguageChooser
     */
    protected function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->session);
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
        return new DispatchGrid($this->serviceEvent, $person, $this->yearCalculator);
    }

    public function titleDefault() {
        $this->setTitle(_('List of events'));
        $this->setIcon('fa fa-calendar');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function startup() {
        /**
         * @var LanguageChooser $languageChooser
         */
        $languageChooser = $this->getComponent('languageChooser');
        $languageChooser->syncRedirect();

        parent::startup();
    }

    /**
     * @return array
     */
    public function getNavBarVariant(): array {
        return ['event', 'bg-dark navbar-dark'];
    }
}
