<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\ORM\ModelContest;
use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use ServiceEvent;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    /**
     *
     * @var ModelEvent
     */
    private $event;

    /**
     * @var int
     * @persistent
     */
    public $eventId;
    /**
     *
     * @var Container
     */
    protected $container;

    /**
     * @var ServiceEvent
     */
    protected $serviceEvent;

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**+
     * @return LanguageChooser
     */
    protected function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->session);
    }

    /**
     * @throws BadRequestException
     */
    protected function startup() {
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser =  $this->getComponent('languageChooser');
        $languageChooser->syncRedirect();

        parent::startup();
    }

    /**
     * @return string
     * @throws BadRequestException
     */
    public function getSubtitle(): string {
        return $this->getEvent()->__toString();
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     */
    protected function getEvent(): ModelEvent {
        if (!$this->event) {
            $row = $this->serviceEvent->findByPrimary($this->eventId);
            if (!$row) {
                throw new BadRequestException('Event not found.', 404);
            }

            $this->event = ModelEvent::createFromTableRow($row);
            $holder = $this->container->createEventHolder($this->event);
            $this->event->setHolder($holder);
        }
        return $this->event;
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function eventIsAllowed($resource, $privilege): bool {
        $event = $this->getEvent();
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function isContestsOrgAllowed($resource, $privilege): bool {
        $contest = $this->getContest();
        if (!$contest) {
            return false;
        }
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $contest);
    }

    protected function getNavBarVariant(): array {
        return ['event event-type-' . $this->getEvent()->event_type_id, ($this->getEvent()->event_type_id == 1) ? 'bg-fyziklani navbar-dark' : 'bg-light navbar-light'];
    }

    protected function getNavRoots(): array {
        return ['event.dashboard.default'];
    }

    /**
     * @return ModelContest
     * @throws BadRequestException
     */
    protected final function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

}
