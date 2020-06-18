<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\UI\PageStyleContainer;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

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
    protected $serviceEvent;
    /**
     * @var EventDispatchFactory
     */
    private $eventDispatchFactory;

    /**
     * @param ServiceEvent $serviceEvent
     * @return void
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    protected function getServiceEvent(): ServiceEvent {
        return $this->serviceEvent;
    }

    /**
     * @param EventDispatchFactory $eventDispatchFactory
     * @return void
     */
    public function injectEventDispatch(EventDispatchFactory $eventDispatchFactory) {
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function getEventDispatchFactory(): EventDispatchFactory {
        return $this->eventDispatchFactory;
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     * @throws \Exception
     */
    protected function startup() {
        if (!$this->isEnabled()) {
            throw new NotImplementedException();
        }
        parent::startup();
    }

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
     * @throws NeonSchemaException
     */
    protected function getHolder(): Holder {
        if (!$this->holder) {
            $this->holder = $this->getEventDispatchFactory()->getDummyHolder($this->getEvent());
        }
        return $this->holder;
    }

    /**
     * @return int
     * @throws BadRequestException
     */
    protected function getAcYear(): int {
        return $this->getYearCalculator()->getAcademicYear($this->getContest(), $this->getEvent()->year);
    }

    /**
     * @return ModelContest
     * @throws BadRequestException
     */
    final protected function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

    protected function isEnabled(): bool {
        return true;
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function isTeamEvent(): bool {
        return (bool)in_array($this->getEvent()->event_type_id, ModelEvent::TEAM_EVENTS);
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
     * @param string $title
     * @param string $icon
     * @param string $subTitle
     * @throws BadRequestException
     */
    protected function setTitle(string $title, string $icon = '', string $subTitle = '') {
        parent::setTitle($title, $icon, $subTitle ?: $this->getEvent()->__toString());
    }

    /**
     * @return PageStyleContainer
     * @throws BadRequestException
     */
    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        $container->styleId = 'event event-type-' . $this->getEvent()->event_type_id;
        switch ($this->getEvent()->event_type_id) {
            case 1:
                $container->navBarClassName = 'bg-fyziklani navbar-dark';
                break;
            case 9:
                $container->navBarClassName = 'bg-fol navbar-light';
                break;
            default:
                $container->navBarClassName = 'bg-light navbar-light';
        }
        return $container;
    }

    /**
     * @return array|string[]
     */
    protected function getNavRoots(): array {
        return ['Event.Dashboard.default'];
    }
}
