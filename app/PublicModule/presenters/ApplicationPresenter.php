<?php

namespace PublicModule;

use Authorization\RelatedPersonAuthorizator;
use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\InitSource;
use Events\Model\Grid\RelatedPersonSource;
use Events\Model\Holder\Holder;
use FKS\Logging\MemoryLogger;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Events\ApplicationsGrid;
use FKSDB\Components\Grids\Events\LayoutResolver;
use Logging\FlashDumpFactory;
use ModelAuthToken;
use ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use ORM\IModel;
use ServiceEvent;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationPresenter extends BasePresenter {

    const PARAM_AFTER = 'a';

    /**
     * @var ModelEvent
     */
    private $event = false;

    /**
     * @var IModel
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
     * @var SystemContainer
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

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function injectRelatedPersonAuthorizator(RelatedPersonAuthorizator $relatedPersonAuthorizator) {
        $this->relatedPersonAuthorizator = $relatedPersonAuthorizator;
    }

    public function injectLayoutResolver(LayoutResolver $layoutResolver) {
        $this->layoutResolver = $layoutResolver;
    }

    public function injectHandlerFactory(ApplicationHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    public function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    public function authorizedDefault($eventId, $id) {
        
    }

    public function authorizedList() {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson());
    }

    public function titleDefault($eventId, $id) {
        if ($this->getEventApplication()) {
            $this->setTitle("{$this->getEvent()} {$this->getEventApplication()}");
        } else {
            $this->setTitle("{$this->getEvent()}");
        }
    }

    public function titleList() {
        $this->setTitle(sprintf(_('Moje přihlášky (%s)'), $this->getSelectedContest()->name));
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

    public function requiresLogin() {
        return $this->getAction() != 'default';
    }

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

        if ($this->getMachine()->getPrimaryMachine()->getState() == BaseMachine::STATE_INIT) {
            if (!$this->getMachine()->getPrimaryMachine()->getAvailableTransitions()) {
                $this->setView('closed');
                $this->flashMessage(_('Přihlašování není povoleno.'), BasePresenter::FLASH_INFO);
            }
        }

        if (!$this->relatedPersonAuthorizator->isRelatedPerson($this->getHolder()) && !$this->getContestAuthorizator()->isAllowed($this->getEvent(), 'application', $this->getEvent()->getContest())) {
            if ($this->getParameter(self::PARAM_AFTER, false)) {
                $this->setView('closed');
            } else {
                throw new ForbiddenRequestException(_('Cizí přihláška.'));
            }
        }
    }

    public function actionList() {
        
    }

    private function initializeMachine() {
        $this->getHolder()->setModel($this->getEventApplication());
        $this->getMachine()->setHolder($this->getHolder());
    }

    protected function createComponentContestChooser($name) {
        $component = parent::createComponentContestChooser($name);
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

    protected function createComponentApplication($name) {
        $logger = new MemoryLogger();
        $handler = $this->handlerFactory->create($this->getEvent(), $logger);
        $flashDump = $this->flashDumpFactory->createApplication();
        $component = new ApplicationComponent($handler, $this->getHolder(), $flashDump);
        $that = $this;
        $component->setRedirectCallback(function($modelId, $eventId) use($that) {
                    $that->backlinkRedirect();
                    $that->redirect('this', array(
                        'eventId' => $eventId,
                        'id' => $modelId,
                        self::PARAM_AFTER => true,
                    ));
                });
        $component->setTemplate($this->layoutResolver->getFormLayout($this->getEvent()));
        return $component;
    }

    protected function createComponentApplicationsGrid($name) {
        $person = $this->getUser()->getIdentity()->getPerson();
        $events = $this->serviceEvent->getTable();
        $events->where('event_type.contest_id', $this->getSelectedContest()->contest_id);

        $source = new RelatedPersonSource($person, $events, $this->container);

        $flashDump = $this->flashDumpFactory->createApplication();
        $grid = new ApplicationsGrid($this->container, $source, $this->handlerFactory, $flashDump);

        $grid->setTemplate('myApplications');

        return $grid;
    }

    protected function createComponentNewApplicationsGrid($name) {
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
            $eventId = $eventId ? : $this->getParameter('eventId');
            $this->event = $this->serviceEvent->findByPrimary($eventId);
        }

        return $this->event;
    }

    private function getEventApplication() {
        if ($this->eventApplication === false) {
            $id = null;
            if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
                $data = $this->getTokenAuthenticator()->getTokenData();
                if ($data) {
                    $data = self::decodeParameters($this->getTokenAuthenticator()->getTokenData());
                    $eventId = $data['id'];
                }
            }
            $id = $id ? : $this->getParameter('id');
            $service = $this->getHolder()->getPrimaryHolder()->getService();
            $this->eventApplication = $service->findByPrimary($id);
        }

        return $this->eventApplication;
    }

    private function getHolder() {
        if (!$this->holder) {
            $this->holder = $this->container->createEventHolder($this->getEvent());
        }
        return $this->holder;
    }

    private function getMachine() {
        if (!$this->machine) {
            $this->machine = $this->container->createEventMachine($this->getEvent());
        }
        return $this->machine;
    }

    public static function encodeParameters($eventId, $id) {
        return "$eventId:$id";
    }

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

}

