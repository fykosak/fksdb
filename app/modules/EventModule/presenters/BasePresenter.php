<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceContestYear;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\YearCalculator;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    const TEAM_EVENTS = [1, 9];

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

    /**
     * @var ServiceContestYear
     */
    protected $serviceContestYear;

    /**
     * @param ServiceContestYear $serviceContestYear
     */
    public function injectServiceContestYear(ServiceContestYear $serviceContestYear) {
        $this->serviceContestYear = $serviceContestYear;
    }

    /**
     * @var YearCalculator
     */
    protected $yearCalculator;

    /**
     * @param YearCalculator $yearCalculator
     */
    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

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
     * @throws AbortException
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
        if (!$this->isEnabledForEvent($this->getEvent())) {
            throw new NotImplementedException();
        }
        parent::startup();
    }

    /**
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    public function isAuthorized(): bool {
        if (!$this->isEnabledForEvent($this->getEvent())) {
            return false;
        }
        return parent::isAuthorized();
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function eventExist(): bool {
        return !!$this->getEvent();
    }

    /**
     * @return int
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function getAcYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getEvent()->getContest(), $this->getEvent()->year);
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws AbortException
     */
    public function getSubTitle(): string {
        return $this->getEvent()->__toString();
    }

    /**
     * @return int
     * @throws AbortException
     */
    protected function getEventId(): int {
        if (!$this->eventId) {
            $this->redirect('Dispatch:default');
        }
        return +$this->eventId;
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function getEvent(): ModelEvent {
        if (!$this->event) {
            $row = $this->serviceEvent->findByPrimary($this->getEventId());
            if (!$row) {
                throw new BadRequestException('Event not found');
            }
            $this->event = ModelEvent::createFromActiveRow($row);
            if ($this->event) {
                $holder = $this->container->createEventHolder($this->getEvent());
                $this->event->setHolder($holder);
            }
        }
        return $this->event;
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function eventIsAllowed($resource, string $privilege): bool {
        $event = $this->getEvent();
        if (!$event) {
            return false;
        }
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function isContestsOrgAllowed($resource, string $privilege): bool {
        $contest = $this->getContest();
        if (!$contest) {
            return false;
        }
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $contest);
    }

    /**
     * @return array
     * @throws BadRequestException
     * @throws AbortException
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
     * @return ModelContest
     * @throws BadRequestException
     * @throws AbortException
     */
    protected final function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

    /**
     * @param ModelEvent $event
     * @return bool
     */
    protected function isEnabledForEvent(ModelEvent $event): bool {
        return true;
    }
}
