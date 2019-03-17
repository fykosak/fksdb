<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    /**
     *
     * @var \FKSDB\ORM\Models\ModelEvent
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
     * @var \FKSDB\ORM\Services\ServiceEvent
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
    protected function startup() {
        /**
         * @var LanguageChooser $languageChooser
         */
        $languageChooser = $this->getComponent('languageChooser');
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
        return !!$this->getEvent();
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function getSubTitle(): string {
        return $this->getEvent()->__toString();
    }

    /**
     * @return int
     * @throws \Nette\Application\AbortException
     */
    protected function getEventId(): int {
        if (!$this->eventId) {
            $this->redirect('Dispatch:default');
        }
        return +$this->eventId;
    }

    /**
     * @return \FKSDB\ORM\Models\ModelEvent
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

    /**
     * @return array
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function getNavBarVariant(): array {
        return ['event event-type-' . $this->getEvent()->event_type_id, ($this->getEvent()->event_type_id == 1) ? 'bg-fyziklani navbar-dark' : 'bg-light navbar-light'];
    }

    /**
     * @return array
     */
    protected function getNavRoots(): array {
        return ['event.dashboard.default'];
    }

    /**
     * @return \FKSDB\ORM\Models\ModelContest
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected final function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

}
