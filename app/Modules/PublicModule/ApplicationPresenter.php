<?php

namespace FKSDB\Modules\PublicModule;

use FKSDB\Authorization\RelatedPersonAuthorizator;
use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Events\Exceptions\EventNotFoundException;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\ApplicationHandlerFactory;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\GoneException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Localization\UnsupportedLanguageException;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\UI\PageTitle;
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
    public const PARAM_AFTER = 'a';

    private ?ModelEvent $event;
    private ?IModel $eventApplication = null;
    private Holder $holder;
    private Machine $machine;
    private ServiceEvent $serviceEvent;
    private RelatedPersonAuthorizator $relatedPersonAuthorizator;
    private ApplicationHandlerFactory $handlerFactory;
    private EventDispatchFactory $eventDispatchFactory;

    final public function injectTernary(
        ServiceEvent $serviceEvent,
        RelatedPersonAuthorizator $relatedPersonAuthorizator,
        ApplicationHandlerFactory $handlerFactory,
        EventDispatchFactory $eventDispatchFactory
    ): void {
        $this->serviceEvent = $serviceEvent;
        $this->relatedPersonAuthorizator = $relatedPersonAuthorizator;
        $this->handlerFactory = $handlerFactory;
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
     * @param int $eventId
     * @param int $id
     * @throws GoneException
     */
    public function authorizedDefault($eventId, $id): void {
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
            $this->setPageTitle(new PageTitle(\sprintf(_('Application for %s: %s'), $this->getEvent()->name, $this->getEventApplication()->__toString()), 'fa fa-calendar-check-o'));
        } else {
            $this->setPageTitle(new PageTitle($this->getEvent(), 'fa fa-calendar-check-o'));
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
            if ($this->getHolder()->getPrimaryHolder()->getModelState() == \FKSDB\Transitions\Machine\Machine::STATE_INIT) {
                return;
            }
        }

        parent::unauthorizedAccess();
    }

    public function requiresLogin(): bool {
        return $this->getAction() != 'default';
    }

    /**
     * @param int $eventId
     * @param int $id
     * @throws AbortException
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws NeonSchemaException
     * @throws NotFoundException
     */
    public function actionDefault($eventId, $id): void {
        if (!$this->getEvent()) {
            throw new EventNotFoundException();
        }
        $eventApplication = $this->getEventApplication();
        if ($id) { // test if there is a new application, case is set there are a edit od application, empty => new application
            if (!$eventApplication) {
                throw new NotFoundException(_('Unknown application.'));
            }
            if (!$eventApplication instanceof IEventReferencedModel) {
                throw new BadTypeException(IEventReferencedModel::class, $eventApplication);
            }
            if ($this->getEvent()->event_id !== $eventApplication->getEvent()->event_id) {
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

            if ($this->getHolder()->getPrimaryHolder()->getModelState() == \FKSDB\Transitions\Machine\Machine::STATE_INIT) {
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
                $this->loginRedirect();
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
     */
    protected function createComponentApplication(): ApplicationComponent {
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
            $eventId = $eventId ?: $this->getParameter('eventId');
            $this->event = $this->serviceEvent->findByPrimary($eventId);
        }

        return $this->event;
    }

    /**
     * @return AbstractModelMulti|AbstractModelSingle|IModel|ModelFyziklaniTeam|ModelEventParticipant|IEventReferencedModel|null
     * @throws NeonSchemaException
     */
    private function getEventApplication(): ?IModel {
        if (!isset($this->eventApplication)) {
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
     * @throws NeonSchemaException
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
