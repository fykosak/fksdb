<?php

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Models\Authorization\RelatedPersonAuthorizator;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Models\Logging\MemoryLogger;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\ReferencedAccessor;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationPresenter extends BasePresenter {

    public const PARAM_AFTER = 'a';
    private ?ModelEvent $event;
    private ?ActiveRow $eventApplication = null;
    private Holder $holder;
    private Machine $machine;
    private ServiceEvent $serviceEvent;
    private RelatedPersonAuthorizator $relatedPersonAuthorizator;
    private EventDispatchFactory $eventDispatchFactory;

    final public function injectTernary(
        ServiceEvent $serviceEvent,
        RelatedPersonAuthorizator $relatedPersonAuthorizator,
        EventDispatchFactory $eventDispatchFactory
    ): void {
        $this->serviceEvent = $serviceEvent;
        $this->relatedPersonAuthorizator = $relatedPersonAuthorizator;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function startup(): void {
        switch ($this->getAction()) {
            case 'edit':
                $this->forward('default', $this->getParameters());
            case 'list':
                $this->forward(':Core:MyApplications:default', $this->getParameters());
            case 'default':
                if (!isset($this->contestId)) {
                    if (!$this->getEvent()) {
                        throw new EventNotFoundException();
                    }
                    // hack if contestId is not present, but there ale a eventId param
                    $this->forward('default', array_merge($this->getParameters(), ['contestId' => $this->getEvent()->getEventType()->contest_id, 'year' => $this->getEvent()->year]));
                }
        }
        $this->yearTraitStartup();
        parent::startup();
    }

    /**
     * @throws GoneException
     */
    public function authorizedDefault(): void {
        /** @var ModelEvent $event */
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

    /**
     * @return void
     * @throws NeonSchemaException
     * @throws \Throwable
     */
    public function titleDefault(): void {
        if ($this->getEventApplication()) {
            $this->setPageTitle(new PageTitle(\sprintf(_('Application for %s: %s'), $this->getEvent()->name, $this->getEventApplication()->__toString()), 'fas fa-calendar-day'));
        } else {
            $this->setPageTitle(new PageTitle($this->getEvent(), 'fas fa-calendar-plus'));
        }
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws NeonSchemaException
     */
    protected function unauthorizedAccess(): void {
        if ($this->getAction() == 'default') {
            $this->initializeMachine();
            if ($this->getHolder()->getPrimaryHolder()->getModelState() == \FKSDB\Models\Transitions\Machine\Machine::STATE_INIT) {
                return;
            }
        }

        parent::unauthorizedAccess();
    }

    public function requiresLogin(): bool {
        return $this->getAction() != 'default';
    }

    /**
     * @param int|null $eventId
     * @param int|null $id
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NeonSchemaException
     * @throws NotFoundException
     */
    public function actionDefault(?int $eventId, ?int $id): void {
        if (!$this->getEvent()) {
            throw new EventNotFoundException();
        }
        $eventApplication = $this->getEventApplication();
        if ($id) { // test if there is a new application, case is set there are a edit od application, empty => new application
            if (!$eventApplication) {
                throw new NotFoundException(_('Unknown application.'));
            }
            $event = ReferencedAccessor::accessModel($eventApplication, ModelEvent::class);
            if ($this->getEvent()->event_id !== $event->event_id) {
                throw new ForbiddenRequestException();
            }
        }

        $this->initializeMachine();

        if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
            $data = $this->tokenAuthenticator->getTokenData();
            if ($data) {
                $this->tokenAuthenticator->disposeTokenData();
                $this->redirect('this', self::decodeParameters($data));
            }
        }

        if (!$this->getMachine()->getPrimaryMachine()->getAvailableTransitions($this->holder, $this->getHolder()->getPrimaryHolder()->getModelState())) {
            if ($this->getHolder()->getPrimaryHolder()->getModelState() == \FKSDB\Models\Transitions\Machine\Machine::STATE_INIT) {
                $this->setView('closed');
                $this->flashMessage(_('Registration is not open.'), BasePresenter::FLASH_INFO);
            } elseif (!$this->getParameter(self::PARAM_AFTER, false)) {
                $this->flashMessage(_('Application machine has no available transitions.'), BasePresenter::FLASH_INFO);
            }
        }

        if (!$this->relatedPersonAuthorizator->isRelatedPerson($this->getHolder()) && !$this->contestAuthorizator->isAllowed($this->getEvent(), 'application', $this->getEvent()->getContest())) {
            if ($this->getParameter(self::PARAM_AFTER, false)) {
                $this->setView('closed');
            } else {
                $this->redirect(':Core:Authentication:login', [
                    'backlink' => $this->storeRequest(),
                    AuthenticationPresenter::PARAM_REASON => $this->getUser()->logoutReason,
                ]);
            }
        }
    }

    /**
     * @return void
     * @throws NeonSchemaException
     */
    private function initializeMachine(): void {
        $this->getHolder()->setModel($this->getEventApplication());
    }

    /**
     * @return ApplicationComponent
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentApplication(): ApplicationComponent {
        $logger = new MemoryLogger();
        $handler = new ApplicationHandler($this->getEvent(), $logger, $this->getContext());
        $component = new ApplicationComponent($this->getContext(), $handler, $this->getHolder());
        $component->setRedirectCallback(function ($modelId, $eventId) {
            $this->backLinkRedirect();
            $this->redirect('this', [
                'eventId' => $eventId,
                'id' => $modelId,
                self::PARAM_AFTER => true,
            ]);
        });
        $component->setTemplate($this->eventDispatchFactory->getFormLayout($this->getEvent()));
        return $component;
    }

    private function getEvent(): ?ModelEvent {
        if (!isset($this->event)) {
            $eventId = null;
            if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_EVENT_NOTIFY)) {
                $data = $this->tokenAuthenticator->getTokenData();
                if ($data) {
                    $data = self::decodeParameters($this->tokenAuthenticator->getTokenData());
                    $eventId = $data['eventId'];
                }
            }
            $eventId = $eventId ?? $this->getParameter('eventId');
            $this->event = $this->serviceEvent->findByPrimary($eventId);
        }

        return $this->event;
    }

    /**
     * @return AbstractModelMulti|AbstractModel|ActiveRow|ModelFyziklaniTeam|ModelEventParticipant|null
     * @throws NeonSchemaException
     */
    private function getEventApplication(): ?ActiveRow {
        if (!isset($this->eventApplication)) {
            $id = $this->getParameter('id');
            $service = $this->getHolder()->getPrimaryHolder()->getService();

            $this->eventApplication = $service->findByPrimary($id);
        }

        return $this->eventApplication;
    }

    /**
     * @return Holder
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    private function getHolder(): Holder {
        if (!isset($this->holder)) {
            $this->holder = $this->eventDispatchFactory->getDummyHolder($this->getEvent());
        }
        return $this->holder;
    }

    private function getMachine(): Machine {
        if (!isset($this->machine)) {
            $this->machine = $this->eventDispatchFactory->getEventMachine($this->getEvent());
        }
        return $this->machine;
    }

    public static function encodeParameters(int $eventId, int $id): string {
        return "$eventId:$id";
    }

    public static function decodeParameters(string $data): array {
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
     * @return void
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     * @throws BadRequestException
     * @throws \ReflectionException
     */
    protected function beforeRender(): void {
        $event = $this->getEvent();
        if ($event) {
            $this->getPageStyleContainer()->styleId = ' event-type-' . $event->event_type_id;
        }
        parent::beforeRender();
    }

    protected function getRole(): string {
        if ($this->getAction() === 'default') {
            return 'selected';
        }
        return parent::getRole();
    }
}
