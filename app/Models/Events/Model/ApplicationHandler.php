<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\MachineExecutionException;
use FKSDB\Models\Events\Exceptions\SubmitProcessingException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\Persons\ModelDataConflictException;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionException;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class ApplicationHandler
{

    public const ERROR_ROLLBACK = 'rollback';
    public const ERROR_SKIP = 'skip';
    public const STATE_TRANSITION = 'transition';
    public const STATE_OVERWRITE = 'overwrite';
    private EventModel $event;

    private Logger $logger;

    private string $errorMode = self::ERROR_ROLLBACK;
    private Connection $connection;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(EventModel $event, Logger $logger, Container $container)
    {
        $this->event = $event;
        $this->logger = $logger;
        $container->callInjects($this);
    }

    public function injectPrimary(Connection $connection, EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->connection = $connection;
    }

    public function setErrorMode(string $errorMode): void
    {
        $this->errorMode = $errorMode;
    }

    public function getMachine(): EventParticipantMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        }
        return $machine;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    final public function store(BaseHolder $holder, ArrayHash $data): void
    {
        $this->innerStoreAndExecute($holder, $data, null, null, self::STATE_OVERWRITE);
    }

    final public function storeAndExecuteValues(BaseHolder $holder, ArrayHash $data): void
    {
        $this->innerStoreAndExecute($holder, $data, null, null, self::STATE_TRANSITION);
    }

    final public function storeAndExecuteForm(
        BaseHolder $holder,
        Form $form,
        ?string $explicitTransitionName = null
    ): void {
        $this->innerStoreAndExecute($holder, null, $form, $explicitTransitionName, self::STATE_TRANSITION);
    }

    /**
     * @throws \Throwable
     */
    final public function onlyExecute(BaseHolder $holder, string $explicitTransitionName): void
    {
        try {
            $this->beginTransaction();
            $transition = $this->getMachine()->getTransitionById($explicitTransitionName);
            if ($transition->source->value !== $holder->getModelState()->value) {
                throw new UnavailableTransitionException($transition, $holder->getModel());
            }
            $this->saveAndExecute($transition, $holder);
        } catch (
            ModelDataConflictException
            | DuplicateApplicationException
            | MachineExecutionException
            | SubmitProcessingException
            | FullCapacityException
            | ExistingPaymentException
            | UnavailableTransitionException $exception
        ) {
            $this->logger->log(new Message($exception->getMessage(), Message::LVL_ERROR));
            $this->reRaise($exception);
        }
    }

    /**
     * @throws \Throwable
     */
    private function innerStoreAndExecute(
        BaseHolder $holder,
        ?ArrayHash $data,
        ?Form $form,
        ?string $explicitTransitionName,
        ?string $execute
    ): void {
        try {
            $this->beginTransaction();

            $transition = $this->processData(
                $data,
                $form,
                $explicitTransitionName
                    ? $this->getMachine()->getTransitionById($explicitTransitionName)
                    : null,
                $holder,
                $execute
            );

            if ($execute === self::STATE_OVERWRITE) {
                if (isset($data[$holder->name]['status'])) {
                    $holder->setModelState(EventParticipantStatus::tryFrom($data[$holder->name]['status']));
                }
            }

            $this->saveAndExecute($transition, $holder);

            if ($data || $form) {
                $this->logger->log(
                    new Message(
                        sprintf(_('Application "%s" saved.'), (string)$holder->getModel()),
                        Message::LVL_SUCCESS
                    )
                );
            }
        } catch (
            ModelDataConflictException |
            DuplicateApplicationException |
            MachineExecutionException |
            SubmitProcessingException |
            FullCapacityException |
            ExistingPaymentException $exception
        ) {
            $this->logger->log(new Message($exception->getMessage(), Message::LVL_ERROR));
            $this->formRollback($form);
            $this->reRaise($exception);
        }
    }

    /**
     * @throws \Throwable
     */
    private function saveAndExecute(?Transition $transition, BaseHolder $holder)
    {
        if ($transition) {
            $this->getMachine()->execute2($transition, $holder);
        }
        $holder->saveModel();
        if ($transition) {
            $transition->callAfterExecute($holder);
        }
        $this->commit();

        if ($transition && $transition->isCreating()) {
            $this->logger->log(
                new Message(
                    sprintf(_('Application "%s" created.'), (string)$holder->getModel()),
                    Message::LVL_SUCCESS
                )
            );
        } elseif ($transition) {
            $this->logger->log(
                new Message(
                    sprintf(
                        _('State of application "%s" changed.'),
                        (string)$holder->getModel()
                    ),
                    Message::LVL_INFO
                )
            );
        }
    }

    private function processData(
        ?ArrayHash $data,
        ?Form $form,
        ?Transition $transition,
        BaseHolder $holder,
        ?string $execute
    ): ?Transition {
        if ($form) {
            $values = FormUtils::emptyStrToNull($form->getValues());
        } else {
            $values = $data;
        }
        Debugger::log(json_encode((array)$values), 'app-form');
        $newState = null;
        if (isset($values[$holder->name]['status'])) {
            $newState = EventParticipantStatus::tryFrom($values[$holder->name]['status']);
        }

        $processState = $holder->processFormValues(
            $values,
            $transition,
            $this->logger,
            $form
        );

        $newState = $newState ?: $processState;

        if ($execute == self::STATE_TRANSITION) {
            if ($newState) {
                $state = $holder->getModelState();
                $transition = $this->getMachine()->getTransitionByTarget(
                    $state,
                    $newState
                );
                if (!$transition) {
                    throw new MachineExecutionException(
                        sprintf(
                            _('There is not a transition from state "%s" of machine "%s" to state "%s".'),
                            $state->label(),
                            $holder->label,
                            $newState->label()
                        )
                    );
                }
            }
        }

        if (isset($values[$holder->name])) {
            $holder->data += (array)$values[$holder->name];
        }

        return $transition;
    }

    private function formRollback(?Form $form): void
    {
        if ($form) {
            /** @var ReferencedId $referencedId */
            foreach ($form->getComponents(true, ReferencedId::class) as $referencedId) {
                $referencedId->rollback();
            }
        }
        $this->rollback();
    }

    public function beginTransaction(): void
    {
        if (!$this->connection->getPdo()->inTransaction()) {
            $this->connection->beginTransaction();
        }
    }

    private function rollback(): void
    {
        if ($this->errorMode === self::ERROR_ROLLBACK) {
            $this->connection->rollBack();
        }
    }

    public function commit(bool $final = false): void
    {
        if ($this->connection->getPdo()->inTransaction() && ($this->errorMode == self::ERROR_ROLLBACK || $final)) {
            $this->connection->commit();
        }
    }

    /**
     * @return never
     * @throws ApplicationHandlerException
     */
    private function reRaise(\Throwable $e): void
    {
        throw new ApplicationHandlerException(_('Error while saving the application.'), 0, $e);
    }
}
