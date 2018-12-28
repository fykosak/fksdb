<?php


namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\Components\Controls\Stalking\Helpers\ContestBadge;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\ORM\ModelPerson;
use Nette\DI\Container;
use ServiceEvent;

class DispatchPresenter extends AuthenticatedPresenter {

    /**
     *
     * @var Container
     */
    protected $container;

    /**
     * @var ServiceEvent
     */
    protected $serviceEvent;

    /**
     * @param Container $container
     */
    public function injectContainer(Container $container) {
        $this->container = $container;
    }

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
        $control = new LanguageChooser($this->session);
        return $control;
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
         * @var $person ModelPerson
         */
        $person = $this->user->getIdentity()->getPerson();
        return new DispatchGrid($this->serviceEvent, $person, $this->yearCalculator);
    }

    public function titleDefault() {
        $this->setTitle(_('List of events'));
        $this->setIcon('fa fa-calendar');
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function startup() {
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        $languageChooser->syncRedirect();

        parent::startup();
    }

    /**
     * @return array
     */
    public function getNavBarVariant(): array {
        return ['event bg-dark', 'dark'];
    }
}
