<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    private ModelEvent $event;
    private Holder $holder;
    protected ServiceEvent $serviceEvent;
    protected EventDispatchFactory $eventDispatchFactory;
    /**
     * @persistent
     */
    public ?int $eventId = null;

    final public function injectEventBase(ServiceEvent $serviceEvent, EventDispatchFactory $eventDispatchFactory): void {
        $this->serviceEvent = $serviceEvent;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @return void
     * @throws AbortException
     * @throws NotImplementedException
     * @throws ForbiddenRequestException
     */
    protected function startup(): void {
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
     * @throws EventNotFoundException
     */
    protected function getEvent(): ModelEvent {
        if (!isset($this->event)) {
            $model = $this->serviceEvent->findByPrimary($this->eventId);
            if (!$model) {
                throw new EventNotFoundException();
            }
            $this->event = $model;
        }
        return $this->event;
    }

    /**
     * @return Holder
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    protected function getHolder(): Holder {
        if (!isset($this->holder)) {
            $this->holder = $this->eventDispatchFactory->getDummyHolder($this->getEvent());
        }
        return $this->holder;
    }

    /**
     * @return int
     * @throws EventNotFoundException
     */
    protected function getAcYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getContest(), $this->getEvent()->year);
    }

    /**
     * @return ModelContest
     * @throws EventNotFoundException
     */
    final protected function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

    protected function isEnabled(): bool {
        return true;
    }

    /**
     * @return bool
     * @throws EventNotFoundException
     */
    protected function isTeamEvent(): bool {
        return in_array($this->getEvent()->event_type_id, ModelEvent::TEAM_EVENTS);
    }

    /* **************** ACL *********************** */
    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * Standard ACL from acl.neon
     * @throws EventNotFoundException
     */
    protected function isContestsOrgAuthorized($resource, ?string $privilege): bool {
        return $this->eventAuthorizator->isContestOrgAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * Check if is contest and event org
     * TODO vyfakuje to aj cartesianov
     * @throws EventNotFoundException
     */
    protected function isEventAndContestOrgAuthorized($resource, ?string $privilege): bool {
        return $this->eventAuthorizator->isEventAndContestOrgAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * Check if has contest permission or is Event org
     * @throws EventNotFoundException
     */
    public function isEventOrContestOrgAuthorized($resource, ?string $privilege): bool {
        return $this->eventAuthorizator->isEventOrContestOrgAllowed($resource, $privilege, $this->getEvent());
    }

    /* ********************** GUI ************************ */
    /**
     * @param PageTitle $pageTitle
     * @return void
     * @throws EventNotFoundException
     */
    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle = $pageTitle->subTitle ?: $this->getEvent()->__toString();
        parent::setPageTitle($pageTitle);
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws UnsupportedLanguageException
     * @throws BadRequestException
     * @throws \ReflectionException
     */
    protected function beforeRender(): void {
        $this->getPageStyleContainer()->styleId = 'event event-type-' . $this->getEvent()->event_type_id;
        switch ($this->getEvent()->event_type_id) {
            case 1:
                $this->getPageStyleContainer()->setNavBarClassName('bg-fyziklani navbar-dark');
                break;
            case 9:
                $this->getPageStyleContainer()->setNavBarClassName('bg-fol navbar-light');
                break;
            default:
                $this->getPageStyleContainer()->setNavBarClassName('bg-light navbar-light');
        }
        parent::beforeRender();
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        return ['Event.Dashboard.default'];
    }
}
