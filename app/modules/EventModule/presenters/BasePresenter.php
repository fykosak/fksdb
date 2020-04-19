<?php

namespace EventModule;

use AuthenticatedPresenter;
use Events\Model\Holder\Holder;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    const TEAM_EVENTS = [1, 9, 13];

    /** @var ModelEvent */
    private $event;
    /** @var Holder */
    private $holder;

    /**
     * @var int
     * @persistent
     */
    public $eventId;

    /** @var ServiceEvent */
    private $serviceEvent;

    /**
     * @param ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @return ServiceEvent
     */
    protected function getServiceEvent(): ServiceEvent {
        return $this->serviceEvent;
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     * @throws \Exception
     */
    protected function startup() {
        if (!$this->isEnabled()) {
            throw new NotImplementedException;
        }
        parent::startup();
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool {
        if (!$this->isEnabled()) {
            return false;
        }
        return parent::isAuthorized();
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     */
    protected function getEvent(): ModelEvent {
        if (!$this->event) {
            $model = $this->getServiceEvent()->findByPrimary($this->eventId);
            if (!$model) {
                throw new NotFoundException('Event not found.');
            }
            $this->event = $model;
        }
        return $this->event;
    }

    /**
     * @return Holder
     * @throws BadRequestException
     */
    protected function getHolder(): Holder {
        if (!$this->holder) {
            $this->holder = $this->getContext()->createEventHolder($this->getEvent());
        }
        return $this->holder;
    }

    /**
     * @return int
     * @throws BadRequestException
     */
    protected function getAcYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getContest(), $this->getEvent()->year);
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
    protected function isEnabled(): bool {
        return true;
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function isTeamEvent(): bool {
        return (bool)in_array($this->getEvent()->event_type_id, self::TEAM_EVENTS);
    }

    /* **************** ACL *********************** */
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

    /* ********************** GUI ************************ */
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
     * @return array
     * @throws BadRequestException
     */
    protected function getNavBarVariant(): array {
        $classNames = ['event event-type-' . $this->getEvent()->event_type_id, null];
        switch ($this->getEvent()->event_type_id) {
            case 1:
                $classNames[1] = 'bg-fyziklani navbar-dark';
                break;
            case 9:
                $classNames[1] = 'bg-fol navbar-light';
                break;
            default:
                $classNames[1] = 'bg-light navbar-light';
        }
        return $classNames;
    }

    /**
     * @return array
     */
    protected function getNavRoots(): array {
        return ['event.dashboard.default'];
    }
}
