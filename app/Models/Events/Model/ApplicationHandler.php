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
use Tracy\Debugger;

final class ApplicationHandler
{
    private EventModel $event;
    public Logger $logger;
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

    public function getMachine(): EventParticipantMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        }
        return $machine;
    }

    /**
     * @throws \Throwable
     */
    final public function storeAndExecuteForm(BaseHolder $holder, Form $form, ?Transition $transition = null): void
    {
        $this->innerStoreAndExecute($holder, $form, $transition);
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
    private function innerStoreAndExecute(BaseHolder $holder, Form $form, ?Transition $explicitTransition): void
    {
        try {
            $this->beginTransaction();
            $transition = $this->processData($form, $explicitTransition, $holder);

            $this->saveAndExecute($transition, $holder);
            $this->logger->log(
                new Message(sprintf(_('Application "%s" saved.'), (string)$holder->getModel()), Message::LVL_SUCCESS)
            );
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
                    sprintf(_('State of application "%s" changed.'), (string)$holder->getModel()),
                    Message::LVL_INFO
                )
            );
        }
    }

    private function processData(Form $form, ?Transition $transition, BaseHolder $holder): ?Transition
    {
        $values = FormUtils::emptyStrToNull($form->getValues());

        Debugger::log(json_encode((array)$values), 'app-form');
        $target = isset($values[$holder->name]['status'])
            ? EventParticipantStatus::tryFrom($values[$holder->name]['status'])
            : ($transition ? $transition->target : null);

        $holder->processFormValues($values, $this->logger, $form);

        if ($target) {
            $source = $holder->getModelState();
            $transition = $this->getMachine()->getTransitionByStates($source, $target);
            if (!$transition) {
                throw new MachineExecutionException(
                    sprintf(
                        _('There is not a transition from state "%s" to state "%s".'),
                        $source->label(),
                        $target->label()
                    )
                );
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
        $this->connection->rollBack();
    }

    public function commit(bool $final = false): void
    {
        if ($this->connection->getPdo()->inTransaction() && $final) {
            $this->connection->commit();
        }
    }

    /**
     * @return never|void
     * @throws ApplicationHandlerException
     */
    private function reRaise(\Throwable $e): void
    {
        throw new ApplicationHandlerException(_('Error while saving the application.'), 0, $e);
    }
}
