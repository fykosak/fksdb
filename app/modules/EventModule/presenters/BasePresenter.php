<?php

namespace EventModule;

use AuthenticatedPresenter;
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

    /**
     * @throws BadRequestException
     * @throws AbortException
     * @throws \Exception
     */
    protected function startup() {

        if (!$this->isEnabledForEvent()) {
            throw new NotImplementedException();
        }
        parent::startup();
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool {
        if (!$this->isEnabledForEvent()) {
            return false;
        }
        return parent::isAuthorized();
    }

    /**
     * @return int
     * @throws BadRequestException
     */
    protected function getAcYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getEvent()->getContest(), $this->getEvent()->year);
    }

    /**
     * @return ModelEvent
     * @return string
     * @throws BadRequestException
     * @throws BadRequestException
     */
    public function getSubTitle(): string {
        return $this->getEvent()->__toString();
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     */
    protected function getEvent(): ModelEvent {
        if (!$this->event) {
            $model = $this->serviceEvent->findByPrimary($this->eventId);
            if (!$model) {
                throw new BadRequestException('Event not found.', 404);
            }

            $this->event = $model;
            $holder = $this->container->createEventHolder($this->event);
            $this->event->setHolder($holder);
        }
        return $this->event;
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     * Standard ACL from acl.neon
     * @throws BadRequestException
     */
    protected function isContestsOrgAuthorized($resource, string $privilege): bool {
        return $this->getEventAuthorizator()->isContestOrgAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     * Check if is contest and event org
     * TODO vyfakuje to aj cartesianov
     */
    protected function isEventAndContestOrgAuthorized($resource, string $privilege): bool {
        return $this->getEventAuthorizator()->isEventAndContestOrgAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     * Check if has contest permission or is Event org
     */
    public function isEventOrContestOrgAuthorized($resource, $privilege): bool {
        return $this->getEventAuthorizator()->isEventOrContestOrgAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @return array
     * @throws BadRequestException
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
     */
    protected final function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

    /**
     * @return bool
     */
    protected function isEnabledForEvent(): bool {
        return true;
    }
}
