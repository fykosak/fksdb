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
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use ORM\IModel;
use ServiceEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationPresenter extends BasePresenter {

    const PARAM_AFTER = 'a';

    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event = false;

    /**
     * @var IModel|\FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam|ModelEventParticipant
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
     * @var FlashDumpFactory
     */
    private $flashDumpFactory;

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
     * @param FlashDumpFactory $flashDumpFactory
     */
    public function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    /**
     * @param $eventId
     * @param $id
     */
    public function authorizedDefault($eventId, $id) {

    }

    public function authorizedList() {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson());
    }

    public function titleDefault() {
        if ($this->getEventApplication()) {
            $this->setTitle(\sprintf(_('Application for %s: %s'), $this->getEvent()->name, $this->getEventApplication()->__toString()));
        } else {
            $this->setTitle("{$this->getEvent()}");
        }
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleList() {
        $contest = $this->getSelectedContest();
        if ($contest) {
            $this->setTitle(sprintf(_('Moje přihlášky (%s)'), $contest->name));
        } else {
            $this->setTitle(_('Moje přihlášky'));
        }
        $this->setIcon('fa fa-calendar');
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
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault($eventId, $id) {
        if (!$this->getEvent()) {
            throw new BadRequestException(_('Neexistující akce.'), 404);
        }
        if ($id && !$this->getEventApplication()) {
            throw new BadRequestException(_('Neexistující přihláška.'), 404);
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
            } else if (!$this->getParameter(self::PARAM_AFTER, false)) {
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
            $component->setContests(array(
                $this->getEvent()->getEventType()->contest_id,
            ));
        } else if ($this->getAction() == 'list') {
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
        $flashDump = $this->flashDumpFactory->createApplication();
        $component = new ApplicationComponent($handler, $this->getHolder(), $flashDump);
        $component->setRedirectCallback(function ($modelId, $eventId) {
            $this->backLinkRedirect();
            $this->redirect('this', array(
                'eventId' => $eventId,
                'id' => $modelId,
                self::PARAM_AFTER => true,
            ));
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

        $flashDump = $this->flashDumpFactory->createApplication();
        $grid = new ApplicationsGrid($this->container, $source, $this->handlerFactory, $flashDump);

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
        $flashDump = $this->flashDumpFactory->createApplication();
        $grid = new ApplicationsGrid($this->container, $source, $this->handlerFactory, $flashDump);
        $grid->setTemplate('myApplications');

        return $grid;
    }

    /**
     * @return \FKSDB\ORM\Models\ModelEvent|\Nette\Database\Table\ActiveRow|null
     */
    private function getEvent() {
        if ($this->event === false) {
            $eventId = null;
            if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
                $data = $this->getTokenAuthenticator()->getTokenData();
                if ($data) {
                    $data = self::decodeParameters($this->getTokenAuthenticator()->getTokenData());
                    $eventId = $data['eventId'];
                }
            }
            $eventId = $eventId ?: $this->getParameter('eventId');
            $this->event = $this->serviceEvent->findByPrimary($eventId);
        }

        return $this->event;
    }

    /**
     * @return ModelEventParticipant|mixed|IModel|\FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam
     */
    private function getEventApplication() {
        if ($this->eventApplication === false) {
            $id = null;
            if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
                $data = $this->getTokenAuthenticator()->getTokenData();
                if ($data) {
                    $data = self::decodeParameters($this->getTokenAuthenticator()->getTokenData());
                    $eventId = $data['id']; // TODO $id?
                }
            }
            $id = $id ?: $this->getParameter('id');
            $service = $this->getHolder()->getPrimaryHolder()->getService();
            $this->eventApplication = $service->findByPrimary($id);
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
        return array(
            'eventId' => $parts[0],
            'id' => $parts[1],
        );
    }

    /**
     * @return array
     */
    public function getNavBarVariant(): array {
        $event = $this->getEvent();
        $parent = parent::getNavBarVariant();;
        if (!$event) {
            return $parent;
        }
        $parent[0] .= ' event-type-' . $event->event_type_id;
        return $parent;
    }

}

