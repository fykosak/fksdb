<?php

namespace PublicModule;

use Authorization\RelatedPersonAuthorizator;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\ApplicationHandlerFactory;
use FKSDB\Events\Model\Grid\InitSource;
use FKSDB\Events\Model\Grid\RelatedPersonSource;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Events\ApplicationsGrid;
use FKSDB\Components\Grids\Events\LayoutResolver;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\GoneException;
use FKSDB\Exceptions\NotFoundException;
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
use FKSDB\UI\PageStyleContainer;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidArgumentException;

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

    public function injectServiceEvent(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectRelatedPersonAuthorizator(RelatedPersonAuthorizator $relatedPersonAuthorizator): void {
        $this->relatedPersonAuthorizator = $relatedPersonAuthorizator;
    }

    public function injectLayoutResolver(LayoutResolver $layoutResolver): void {
        $this->layoutResolver = $layoutResolver;
    }

    public function injectHandlerFactory(ApplicationHandlerFactory $handlerFactory): void {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @var EventDispatchFactory
     */
    private $eventDispatchFactory;

    public function injectEventDispatch(EventDispatchFactory $eventDispatchFactory): void {
        $this->eventDispatchFactory = $eventDispatchFactory;
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
            throw new GoneException();
        }
    }

    public function authorizedList() {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson());
    }

    /**
     * @throws \Throwable
     */
    public function titleDefault() {
        if ($this->getEventApplication()) {
            $this->setTitle(\sprintf(_('Application for %s: %s'), $this->getEvent()->name, $this->getEventApplication()->__toString()), 'fa fa-calendar-check-o');
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
            $this->setTitle(\sprintf(_('Moje přihlášky (%s)'), $contest->name), 'fa fa-calendar');
        } else {
            $this->setTitle(_('Moje přihlášky'), 'fa fa-calendar');
        }
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws NeonSchemaException
     */
    protected function unauthorizedAccess() {
        if ($this->getAction() == 'default') {
            $this->initializeMachine();
            if ($this->getHolder()->getPrimaryHolder()->getModelState() == BaseMachine::STATE_INIT) {
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
     * @throws NeonSchemaException
     */
    public function actionDefault($eventId, $id) {
        if (!$this->getEvent()) {
            throw new NotFoundException(_('Neexistující akce.'));
        }
        $eventApplication = $this->getEventApplication();
        if ($id) { // test if there is a new application, case is set there are a edit od application, empty => new application
            if (!$eventApplication) {
                throw new NotFoundException(_('Neexistující přihláška.'));
            }
            if (!$eventApplication instanceof IEventReferencedModel) {
                throw new BadTypeException(IEventReferencedModel::class, $eventApplication);
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


        if (!$this->getMachine()->getPrimaryMachine()->getAvailableTransitions($this->holder, $this->getHolder()->getPrimaryHolder()->getModelState())) {

            if ($this->getHolder()->getPrimaryHolder()->getModelState() == BaseMachine::STATE_INIT) {
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

    /**
     * @return void
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    private function initializeMachine() {
        $this->getHolder()->setModel($this->getEventApplication());
    }

    /**
     * @return ContestChooser
     * @throws BadRequestException
     */
    protected function createComponentContestChooser(): ContestChooser {
        $component = parent::createComponentContestChooser();
        if ($this->getAction() == 'default') {
            if (!$this->getEvent()) {
                throw new NotFoundException(_('Neexistující akce.'));
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
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    protected function createComponentApplication() {
        $logger = new MemoryLogger();
        $handler = $this->handlerFactory->create($this->getEvent(), $logger);
        $component = new ApplicationComponent($this->getContext(), $handler, $this->getHolder());
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

        $source = new RelatedPersonSource($person, $events, $this->getContext());

        $grid = new ApplicationsGrid($this->getContext(), $source, $this->handlerFactory);

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

        $source = new InitSource($events, $this->getContext());
        $grid = new ApplicationsGrid($this->getContext(), $source, $this->handlerFactory);
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
            $event = $this->serviceEvent->findByPrimary($eventId);
            if ($event) {
                $this->event = $event;
            }
        }

        return $this->event;
    }

    /**
     * @return AbstractModelMulti|AbstractModelSingle|IModel|ModelFyziklaniTeam|ModelEventParticipant|IEventReferencedModel
     * @throws BadRequestException
     * @throws NeonSchemaException
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
                 $this->eventApplication = ($service->getModelClassName())::createFromActiveRow($row);
             }*/
        }

        return $this->eventApplication;
    }

    /**
     * @return Holder
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    private function getHolder() {
        if (!$this->holder) {
            $this->holder = $this->eventDispatchFactory->getDummyHolder($this->getEvent());
        }
        return $this->holder;
    }

    /**
     * @return Machine
     * @throws BadRequestException
     */
    private function getMachine() {
        if (!$this->machine) {
            $this->machine = $this->eventDispatchFactory->getEventMachine($this->getEvent());
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

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        $event = $this->getEvent();
        if (!$event) {
            return $container;
        }
        $container->styleId = ' event-type-' . $event->event_type_id;
        return $container;
    }
}
