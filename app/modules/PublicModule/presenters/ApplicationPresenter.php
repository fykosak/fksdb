<?php

namespace PublicModule;

use Authorization\RelatedPersonAuthorizator;
use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\InitSource;
use Events\Model\Grid\RelatedPersonSource;
use Events\Model\Holder\Holder;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Events\ApplicationsGrid;
use FKSDB\Components\Grids\Events\LayoutResolver;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use function sprintf;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationPresenter extends BasePresenter {

    const PARAM_AFTER = 'a';

    /**
     * @var ModelEvent|null
     */
    private $event;

    /**
     * @var IModel|ModelFyziklaniTeam|ModelEventParticipant
     */
    private $eventApplication = false;

    /**
     * @var Holder
     */
    private $holder;

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var RelatedPersonAuthorizator
     */
    private $relatedPersonAuthorizator;

    /**
     * @var LayoutResolver
     */
    private $layoutResolver;

    /**
     * @var ApplicationHandlerFactory
     */
    private $handlerFactory;

    /**
     * @param ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param Container $container
     */
    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    /**
     * @param RelatedPersonAuthorizator $relatedPersonAuthorizator
     */
    public function injectRelatedPersonAuthorizator(RelatedPersonAuthorizator $relatedPersonAuthorizator) {
        $this->relatedPersonAuthorizator = $relatedPersonAuthorizator;
    }

    /**
     * @param LayoutResolver $layoutResolver
     */
    public function injectLayoutResolver(LayoutResolver $layoutResolver) {
        $this->layoutResolver = $layoutResolver;
    }

    /**
     * @param ApplicationHandlerFactory $handlerFactory
     */
    public function injectHandlerFactory(ApplicationHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param $eventId
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedDefault($eventId, $id) {
        /**
         * @var ModelEvent $event
         */
        $event = $this->getEvent();
        if ($this->contestAuthorizator->isAllowed('event.participant', 'edit', $event->getContest())
            || $this->contestAuthorizator->isAllowed('fyziklani.team', 'edit', $event->getContest())) {
            $this->setAuthorized(true);
            return;
        }
        if (strtotime($event->registration_begin) > time() || strtotime($event->registration_end) < time()) {
            throw new BadRequestException('Gone', 410);
        }
    }

    public function authorizedList() {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson());
    }

    public function titleDefault() {
        if ($this->getEventApplication()) {
            $this->setTitle(sprintf(_('Application for %s: %s'), $this->getEvent()->name, $this->getEventApplication()->__toString()), 'fa fa-calendar-check-o');
        } else {
            $this->setTitle($this->getEvent(), 'fa fa-calendar-check-o');
        }
    }

    /**
     * @throws BadRequestException
     */
    public function titleList() {
        $contest = $this->getSelectedContest();
        if ($contest) {
            $this->setTitle(sprintf(_('Moje přihlášky (%s)'), $contest->name), 'fa fa-calendar');
        } else {
            $this->setTitle(_('Moje přihlášky'), 'fa fa-calendar');
        }
    }

    protected function unauthorizedAccess() {
        if ($this->getAction() == 'default') {
            $this->initializeMachine();
            if ($this->getMachine()->getPrimaryMachine()->getState() == BaseMachine::STATE_INIT) {
                return;
            }
        }

        parent::unauthorizedAccess();
    }

    /**
     * @return bool
     */
    public function requiresLogin() {
        return $this->getAction() != 'default';
    }

    /**
     * @param $eventId
     * @param $id
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionDefault($eventId, $id) {
        if (!$this->getEvent()) {
            throw new BadRequestException(_('Neexistující akce.'), 404);
        }
        $eventApplication = $this->getEventApplication();
        if ($id) { // test if there is a new application, case is set there are a edit od application, empty => new application
            if (!$eventApplication) {
                throw new BadRequestException(_('Neexistující přihláška.'), 404);
            }
            if (!$eventApplication instanceof IEventReferencedModel) {
                throw new BadRequestException();
            }
            if ($this->getEvent()->event_id !== $eventApplication->getEvent()->event_id) {
                throw new ForbiddenRequestException();
            }
        }

        $this->initializeMachine();

        if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
            $data = $this->getTokenAuthenticator()->getTokenData();
            if ($data) {
                $this->getTokenAuthenticator()->disposeTokenData();
                $this->redirect('this', self::decodeParameters($data));
            }
        }


        if (!$this->getMachine()->getPrimaryMachine()->getAvailableTransitions()) {
            if ($this->getMachine()->getPrimaryMachine()->getState() == BaseMachine::STATE_INIT) {
                $this->setView('closed');
                $this->flashMessage(_('Přihlašování není povoleno.'), BasePresenter::FLASH_INFO);
            } elseif (!$this->getParameter(self::PARAM_AFTER, false)) {
                $this->flashMessage(_('Automat přihlášky nemá aktuálně žádné možné přechody.'), BasePresenter::FLASH_INFO);
            }
        }

        if (!$this->relatedPersonAuthorizator->isRelatedPerson($this->getHolder()) && !$this->getContestAuthorizator()->isAllowed($this->getEvent(), 'application', $this->getEvent()->getContest())) {
            if ($this->getParameter(self::PARAM_AFTER, false)) {
                $this->setView('closed');
            } else {
                $this->loginRedirect();
            }
        }
    }

    /**
     * @throws BadRequestException
     */
    public function actionList() {
        if (!$this->getSelectedContest()) {
            $this->setView('contestChooser');
        }
    }

    private function initializeMachine() {
        $this->getHolder()->setModel($this->getEventApplication());
        $this->getMachine()->setHolder($this->getHolder());
    }

    /**
     * @return ContestChooser
     * @throws BadRequestException
     */
    protected function createComponentContestChooser(): ContestChooser {
        $component = parent::createComponentContestChooser();
        if ($this->getAction() == 'default') {
            if (!$this->getEvent()) {
                throw new BadRequestException(_('Neexistující akce.'), 404);
            }
            $component->setContests([
                $this->getEvent()->getEventType()->contest_id,
            ]);
        } elseif ($this->getAction() == 'list') {
            $component->setContests(ContestChooser::CONTESTS_ALL);
        }
        return $component;
    }

    /**
     * @return ApplicationComponent
     */
    protected function createComponentApplication() {
        $logger = new MemoryLogger();
        $handler = $this->handlerFactory->create($this->getEvent(), $logger);
        $component = new ApplicationComponent($handler, $this->getHolder());
        $component->setRedirectCallback(function ($modelId, $eventId) {
            $this->backLinkRedirect();
            $this->redirect('this', [
                'eventId' => $eventId,
                'id' => $modelId,
                self::PARAM_AFTER => true,
            ]);
        });
        $component->setTemplate($this->layoutResolver->getFormLayout($this->getEvent()));
        return $component;
    }

    /**
     * @return ApplicationsGrid
     * @throws BadRequestException
     */
    protected function createComponentApplicationsGrid() {
        $person = $this->getUser()->getIdentity()->getPerson();
        $events = $this->serviceEvent->getTable();
        $events->where('event_type.contest_id', $this->getSelectedContest()->contest_id);

        $source = new RelatedPersonSource($person, $events, $this->container);

        $grid = new ApplicationsGrid($this->container, $source, $this->handlerFactory);

        $grid->setTemplate('myApplications');

        return $grid;
    }

    /**
     * @return ApplicationsGrid
     * @throws BadRequestException
     */
    protected function createComponentNewApplicationsGrid() {
        $events = $this->serviceEvent->getTable();
        $events->where('event_type.contest_id', $this->getSelectedContest()->contest_id)
            ->where('registration_begin <= NOW()')
            ->where('registration_end >= NOW()');

        $source = new InitSource($events, $this->container);
        $grid = new ApplicationsGrid($this->container, $source, $this->handlerFactory);
        $grid->setTemplate('myApplications');

        return $grid;
    }

    /**
     * @return ModelEvent|null
     */
    private function getEvent() {
        if (!$this->event) {
            $eventId = null;
            if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
                $data = $this->getTokenAuthenticator()->getTokenData();
                if ($data) {
                    $data = self::decodeParameters($this->getTokenAuthenticator()->getTokenData());
                    $eventId = $data['eventId'];
                }
            }
            $eventId = $eventId ?: $this->getParameter('eventId');
            $row = $this->serviceEvent->findByPrimary($eventId);
            if ($row) {
                $this->event = ModelEvent::createFromTableRow($row);
            }
        }

        return $this->event;
    }

    /**
     * @return AbstractModelMulti|AbstractModelSingle|IModel|ModelFyziklaniTeam|ModelEventParticipant|IEventReferencedModel
     */
    private function getEventApplication() {
        if (!$this->eventApplication) {
            $id = null;
            //if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
            //   $data = $this->getTokenAuthenticator()->getTokenData();
            //   if ($data) {
            //    $data = self::decodeParameters($this->getTokenAuthenticator()->getTokenData());
            //$eventId = $data['id']; // TODO $id?
            //  }
            // }
            $id = $id ?: $this->getParameter('id');
            $service = $this->getHolder()->getPrimaryHolder()->getService();

            $this->eventApplication = $service->findByPrimary($id);
            /* if ($row) {
                 $this->eventApplication = ($service->getModelClassName())::createFromTableRow($row);
             }*/
        }

        return $this->eventApplication;
    }

    /**
     * @return Holder
     */
    private function getHolder() {
        if (!$this->holder) {
            $this->holder = $this->container->createEventHolder($this->getEvent());
        }
        return $this->holder;
    }

    /**
     * @return Machine
     */
    private function getMachine() {
        if (!$this->machine) {
            $this->machine = $this->container->createEventMachine($this->getEvent());
        }
        return $this->machine;
    }

    /**
     * @param $eventId
     * @param $id
     * @return string
     */
    public static function encodeParameters($eventId, $id) {
        return "$eventId:$id";
    }

    /**
     * @param $data
     * @return array
     */
    public static function decodeParameters($data) {
        $parts = explode(':', $data);
        if (count($parts) != 2) {
            throw new InvalidArgumentException("Cannot decode '$data'.");
        }
        return [
            'eventId' => $parts[0],
            'id' => $parts[1],
        ];
    }

    /**
     * @return array
     */
    public function getNavBarVariant(): array {
        $event = $this->getEvent();
        $parent = parent::getNavBarVariant();
        if (!$event) {
            return $parent;
        }
        $parent[0] .= ' event-type-' . $event->event_type_id;
        return $parent;
    }

}

