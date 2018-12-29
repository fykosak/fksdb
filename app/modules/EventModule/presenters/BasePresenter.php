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
     * @throws \Nette\Application\AbortException
     */
    public function startup() {
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        $languageChooser->syncRedirect();

        if (!$this->eventExist()) {
            throw new BadRequestException('Event not found.', 404);
        }
        parent::startup();
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function eventExist(): bool {
        return $this->getEvent() ? true : false;
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function getSubtitle(): string {
        return $this->getEvent()->name;
    }

    /**
     * @return int
     * @throws \Nette\Application\AbortException
     */
    public function getEventId(): int {
        if (!$this->eventId) {
            $this->redirect('Dispatch:default');
        }
        return +$this->eventId;
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function getEvent(): ModelEvent {
        if (!$this->event) {
            $row = $this->serviceEvent->findByPrimary($this->getEventId());
            if (!$row) {
                throw new BadRequestException('Event not found');
            }
            $this->event = ModelEvent::createFromTableRow($row);
            if ($this->event) {
                $holder = $this->container->createEventHolder($this->getEvent());
                $this->event->setHolder($holder);
            }
        }
        return $this->event;
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function eventIsAllowed($resource, $privilege): bool {
        $event = $this->getEvent();
        if (!$event) {
            return false;
        }
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function isContestsOrgAllowed($resource, $privilege): bool {
        $contest = $this->getContest();
        if (!$contest) {
            return false;
        }
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $contest);
    }

    public function getNavBarVariant(): array {
        return ['event event-type-' . $this->getEvent()->event_type_id, ($this->getEvent()->event_type_id == 1) ? 'dark' : 'light'];
    }

    public function getNavRoots(): array {
        return ['event.dashboard.default'];
    }

    /**
     * @return ModelContest
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected final function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

}
