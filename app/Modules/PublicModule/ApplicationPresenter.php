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
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
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
    private EventParticipantService $eventParticipantService;

    public static function encodeParameters(int $eventId, int $id): string
    {
        return "$eventId:$id";
    }

    final public function injectTernary(
        EventService $eventService,
        RelatedPersonAuthorizator $relatedPersonAuthorizator,
        EventDispatchFactory $eventDispatchFactory,
        EventParticipantService $eventParticipantService
    ): void {
        $this->eventService = $eventService;
        $this->relatedPersonAuthorizator = $relatedPersonAuthorizator;
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @throws EventNotFoundException
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
     * @throws GoneException|EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        $event = $this->getEvent();
        if ($this->eventAuthorizator->isAllowed('event.participant', 'edit', $event)) {
            return true;
        }
        if (
            (isset($event->registration_begin) && strtotime((string)$event->registration_begin) > time())
            || (isset($event->registration_end) && strtotime((string)$event->registration_end) < time())
        ) {
            throw new GoneException();
        }
        return true;
    }

    private function getEventApplication(): ?EventParticipantModel
    {
        if (!isset($this->eventApplication)) {
            $id = $this->getParameter('id');
            $this->eventApplication = $this->eventParticipantService->findByPrimary($id);
        }

        return $this->eventApplication;
    }

    /**
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

    /**
     * @throws EventNotFoundException
     */
    public function requiresLogin(): bool
    {
        if ($this->getAction() == 'default') {
            $this->initializeMachine();
            if ($this->getHolder()->getModelState() == Machine::STATE_INIT) {
                return false;
            }
        }
        return true;
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
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

        if (!$this->getMachine()->getAvailableTransitions($this->getHolder(), $this->getHolder()->getModelState())) {
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
            $machine = $this->eventDispatchFactory->getParticipantMachine($this->getEvent());
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
                $this->forward(':Profile:MyApplications:default', $this->getParameters());
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
     * @throws EventNotFoundException
     */
    private function initializeMachine(): void
    {
        $this->getHolder()->setModel($this->getEventApplication());
    }

    /**
     * @throws ConfigurationNotFoundException
     * @throws EventNotFoundException
     */
    protected function createComponentApplication(): ApplicationComponent
    {
        return new ApplicationComponent($this->getContext(), $this->getHolder());
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->model = $this->getEventApplication();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function getStyleId(): string
    {
        $contest = $this->getSelectedContest();
        if (isset($contest)) {
            return 'contest-' . $contest->getContestSymbol() . ' event-type-' . $this->getEvent()->event_type_id;
        }
        return parent::getStyleId();
    }

    protected function getRole(): PresenterRole
    {
        if ($this->getAction() === 'default') {
            return PresenterRole::tryFrom(PresenterRole::SELECTED);
        }
        return parent::getRole();
    }
}
