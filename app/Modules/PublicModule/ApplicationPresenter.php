<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Models\Authorization\RelatedPersonAuthorizator;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\WebService\AESOP\Models\EventParticipantModel;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidArgumentException;

class ApplicationPresenter extends BasePresenter
{

    public const PARAM_AFTER = 'a';
    private ?EventModel $event;
    private ?Model $eventApplication = null;
    private EventService $eventService;
    private RelatedPersonAuthorizator $relatedPersonAuthorizator;
    private EventDispatchFactory $eventDispatchFactory;

    public static function encodeParameters(int $eventId, int $id): string
    {
        return "$eventId:$id";
    }

    final public function injectTernary(
        EventService $eventService,
        RelatedPersonAuthorizator $relatedPersonAuthorizator,
        EventDispatchFactory $eventDispatchFactory
    ): void {
        $this->eventService = $eventService;
        $this->relatedPersonAuthorizator = $relatedPersonAuthorizator;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @throws GoneException|EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $event = $this->getEvent();
        if (
            $this->eventAuthorizator->isAllowed('event.participant', 'edit', $event)
            || $this->eventAuthorizator->isAllowed('fyziklani.team', 'edit', $event)
        ) {
            $this->setAuthorized(true);
            return;
        }
        if (
            (isset($event->registration_begin) && strtotime((string)$event->registration_begin) > time())
            || (isset($event->registration_end) && strtotime((string)$event->registration_end) < time())
        ) {
            throw new GoneException();
        }
    }

    /**
     * @throws NeonSchemaException
     * @throws \Throwable
     */
    public function titleDefault(): PageTitle
    {
        if ($this->getEventApplication()) {
            return new PageTitle(
                null,
                \sprintf(
                    _('Application for %s: %s'),
                    $this->getEvent()->name,
                    $this->getEventApplication()->__toString()
                ),
                'fas fa-calendar-day',
            );
        } else {
            return new PageTitle(null, $this->getEvent()->__toString(), 'fas fa-calendar-plus');
        }
    }

    /**
     * @return EventParticipantModel|null
     * @throws NeonSchemaException
     * @throws EventNotFoundException
     */
    private function getEventApplication(): ?Model
    {
        if (!isset($this->eventApplication)) {
            $id = $this->getParameter('id');
            $service = $this->getHolder()->service;

            $this->eventApplication = $service->findByPrimary($id);
        }

        return $this->eventApplication;
    }

    /**
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     * @throws EventNotFoundException
     */
    private function getHolder(): BaseHolder
    {
        static $holder;
        if (!isset($holder) || $holder->event->event_id !== $this->getEvent()->event_id) {
            $holder = $this->eventDispatchFactory->getDummyHolder($this->getEvent());
        }
        return $holder;
    }

    public function requiresLogin(): bool
    {
        return $this->getAction() != 'default';
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NeonSchemaException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function actionDefault(?int $eventId, ?int $id): void
    {
        $eventApplication = $this->getEventApplication();
        if ($id) {
            // test if there is a new application, case is set there are a edit od application, empty => new application
            if (!$eventApplication) {
                throw new NotFoundException(_('Unknown application.'));
            }
            /** @var EventModel $event */
            $event = $eventApplication->getReferencedModel(EventModel::class);
            if ($this->getEvent()->event_id !== $event->event_id) {
                throw new ForbiddenRequestException();
            }
        }

        $this->initializeMachine();

        if ($this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::EventNotify)) {
            $data = $this->tokenAuthenticator->getTokenData();
            if ($data) {
                $this->tokenAuthenticator->disposeTokenData();
                $this->redirect('this', self::decodeParameters($data));
            }
        }

        if (
            !$this->getMachine()
                ->getAvailableTransitions(
                    $this->getHolder(),
                    $this->getHolder()->getModelState()
                )
        ) {
            if (
                $this->getHolder()->getModelState() == Machine::STATE_INIT
            ) {
                $this->setView('closed');
                $this->flashMessage(_('Registration is not open.'), Message::LVL_INFO);
            } elseif (!$this->getParameter(self::PARAM_AFTER, false)) {
                $this->flashMessage(_('Application machine has no available transitions.'), Message::LVL_INFO);
            }
        }

        if (
            !$this->relatedPersonAuthorizator->isRelatedPerson($this->getHolder()) &&
            !$this->eventAuthorizator->isAllowed(
                $this->getEvent(),
                'application',
                $this->getEvent()
            )
        ) {
            if ($this->getParameter(self::PARAM_AFTER, false)) {
                $this->setView('closed');
            } else {
                $this->redirect(
                    ':Core:Authentication:login',
                    [
                        'backlink' => $this->storeRequest(),
                        AuthenticationPresenter::PARAM_REASON => $this->getUser()->logoutReason,
                    ]
                );
            }
        }
    }

    /**
     * @throws EventNotFoundException
     */
    private function getMachine(): EventParticipantMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->eventDispatchFactory->getEventMachine($this->getEvent());
        }
        return $machine;
    }

    protected function startup(): void
    {
        switch ($this->getAction()) {
            case 'edit':
                $this->forward('default', $this->getParameters());
                break;
            case 'list':
                $this->forward(':Profile:List:default', $this->getParameters());
                break;
            case 'default':
                if (!isset($this->contestId)) {
                    // hack if contestId is not present, but there ale a eventId param
                    $this->forward(
                        'default',
                        array_merge(
                            $this->getParameters(),
                            [
                                'contestId' => $this->getEvent()->event_type->contest_id,
                                'year' => $this->getEvent()->year,
                            ]
                        )
                    );
                }
        }
        $this->yearTraitStartup();
        parent::startup();
    }

    /**
     * @throws EventNotFoundException
     */
    private function getEvent(): EventModel
    {
        if (!isset($this->event)) {
            $eventId = null;
            if ($this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::EventNotify)) {
                $data = $this->tokenAuthenticator->getTokenData();
                if ($data) {
                    $data = self::decodeParameters($this->tokenAuthenticator->getTokenData());
                    $eventId = $data['eventId'];
                }
            }
            $eventId = $eventId ?? $this->getParameter('eventId');
            $this->event = $this->eventService->findByPrimary($eventId);
        }
        if (!isset($this->event)) {
            throw new EventNotFoundException();
        }

        return $this->event;
    }

    public static function decodeParameters(string $data): array
    {
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
     * @throws ForbiddenRequestException
     * @throws NeonSchemaException
     * @throws EventNotFoundException
     */
    protected function unauthorizedAccess(): void
    {
        if ($this->getAction() == 'default') {
            $this->initializeMachine();
            if (
                $this->getHolder()->getModelState() == Machine::STATE_INIT
            ) {
                return;
            }
        }
        parent::unauthorizedAccess();
    }

    /**
     * @throws NeonSchemaException
     * @throws EventNotFoundException
     */
    private function initializeMachine(): void
    {
        $this->getHolder()->setModel($this->getEventApplication());
    }

    /**
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     * @throws EventNotFoundException
     */
    protected function createComponentApplication(): ApplicationComponent
    {
        $logger = new MemoryLogger();
        $handler = new ApplicationHandler($this->getEvent(), $logger, $this->getContext());
        $component = new ApplicationComponent($this->getContext(), $handler, $this->getHolder());
        $component->setRedirectCallback(
            function ($modelId, $eventId) {
                // $this->backLinkRedirect();
                $this->redirect(
                    'this',
                    [
                        'eventId' => $eventId,
                        'id' => $modelId,
                        self::PARAM_AFTER => true,
                    ]
                );
            }
        );
        $component->setTemplate($this->eventDispatchFactory->getFormLayout($this->getEvent()));
        return $component;
    }

    /**
     * @throws BadRequestException
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();
        $event = $this->getEvent();
        $this->getPageStyleContainer()->styleIds[] = 'event event-type-' . $event->event_type_id;
        switch ($event->event_type_id) {
            case 1:
                $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-fof');
                break;
            case 9:
                $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-fol');
                break;
            default:
                $this->getPageStyleContainer()->setNavBarClassName(
                    'navbar-dark bg-' . $event->event_type->contest->getContestSymbol()
                );
        }
    }

    protected function getRole(): PresenterRole
    {
        if ($this->getAction() === 'default') {
            return PresenterRole::tryFrom(PresenterRole::SELECTED);
        }
        return parent::getRole();
    }
}
