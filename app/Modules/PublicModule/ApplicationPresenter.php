<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Models\Authorization\RelatedPersonAuthorizator;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidArgumentException;

final class ApplicationPresenter extends BasePresenter
{
    public const PARAM_AFTER = 'a';
    private ?EventModel $event;
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
                    $this->getEventApplication()->person->getFullName()
                ),
                'fas fa-calendar-day',
            );
        } else {
            return new PageTitle(null, $this->getEvent()->name, 'fas fa-calendar-plus');
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
        if (!$event->isRegistrationOpened()) {
            throw new GoneException();
        }
        return true;
    }

    private function getEventApplication(): ?EventParticipantModel
    {
        $id = $this->getParameter('id');
        /** @var EventParticipantModel|null $eventApplication */
        $eventApplication = $this->eventParticipantService->findByPrimary($id);
        return $eventApplication;
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
     * @throws BadTypeException
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

        if (
            $this->tokenAuthenticator->isAuthenticatedByToken(
                AuthTokenType::from(AuthTokenType::EVENT_NOTIFY)
            )
        ) {
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
     * @throws BadTypeException
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
        if (in_array($this->getEvent()->event_type_id, [2, 14, 11, 12])) {
            if ($this->getEventApplication()) {
                $this->redirect(
                    ':Event:Application:edit',
                    [
                        'eventId' => $this->getEvent()->event_id,
                        'id' => $this->getEventApplication()->event_participant_id,
                    ]
                );
            } else {
                $this->redirect(':Event:Application:create', ['eventId' => $this->getEvent()->event_id, 'id' => null]);
            }
        }
        switch ($this->getAction()) {
            case 'edit':
                $this->forward('default', $this->getParameters());
                break; // @phpstan-ignore-line
            case 'list':
                $this->forward(':Profile:MyApplications:default', $this->getParameters());
                break; // @phpstan-ignore-line
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
            if (
                $this->tokenAuthenticator->isAuthenticatedByToken(
                    AuthTokenType::from(AuthTokenType::EVENT_NOTIFY)
                )
            ) {
                $data = $this->tokenAuthenticator->getTokenData();
                if ($data) {
                    $data = self::decodeParameters($data);
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

    /**
     * @phpstan-return int[]
     */
    public static function decodeParameters(string $data): array
    {
        $parts = explode(':', $data);
        if (count($parts) != 2) {
            throw new InvalidArgumentException("Cannot decode '$data'.");
        }
        return [
            'eventId' => (int)$parts[0],
            'id' => (int)$parts[1],
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
     * @throws BadTypeException
     */
    protected function createComponentApplication(): ApplicationComponent
    {
        return new ApplicationComponent($this->getContext(), $this->getHolder(), $this->getMachine());
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->model = $this->getEventApplication();
    }

    /**
     * @throws EventNotFoundException
     * @throws NoContestAvailable
     * @throws NoContestAvailable
     */
    protected function getStyleId(): string
    {
        try {
            $contest = $this->getSelectedContest();
            return 'contest-' . $contest->getContestSymbol() . ' event-type-' . $this->getEvent()->event_type_id;
        } catch (NoContestAvailable$exception) {
            return parent::getStyleId();
        }
    }

    protected function getRole(): PresenterRole
    {
        if ($this->getAction() === 'default') {
            return PresenterRole::from(PresenterRole::SELECTED);
        }
        return parent::getRole();
    }
}
