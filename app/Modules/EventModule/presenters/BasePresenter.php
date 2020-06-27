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
use FKSDB\UI\PageTitle;
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
     * @param PageTitle $pageTitle
     * @return void
     * @throws BadRequestException
     */
    protected function setPageTitle(PageTitle $pageTitle) {
        $pageTitle->subTitle = $pageTitle->subTitle ?: $this->getEvent()->__toString();
        parent::setPageTitle($pageTitle);
    }

    protected function beforeRender() {
        $this->getPageStyleContainer()->styleId = 'event event-type-' . $this->getEvent()->event_type_id;
        switch ($this->getEvent()->event_type_id) {
            case 1:
                $this->getPageStyleContainer()->navBarClassName = 'bg-fyziklani navbar-dark';
                break;
            case 9:
                $this->getPageStyleContainer()->navBarClassName = 'bg-fol navbar-light';
                break;
            default:
                $this->getPageStyleContainer()->navBarClassName = 'bg-light navbar-light';
        }
        parent::beforeRender();
    }

    /**
     * @return array|string[]
     */
    protected function getNavRoots(): array {
        return ['Event.Dashboard.default'];
    }
}
