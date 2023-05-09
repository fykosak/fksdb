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
    private EventModel $event;
    private Logger $logger;

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
            $machine = $this->eventDispatchFactory->getParticipantMachine($this->event);
        }
        return $machine;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @throws \Throwable
     */
    final public function storeAndExecuteForm(BaseHolder $holder, Form $form, ?string $transitionName): void
    {
        try {
            if (!$this->connection->getPdo()->inTransaction()) {
                $this->connection->beginTransaction();
            }

            $transition = $this->processData(
                FormUtils::emptyStrToNull($form->getValues()),
                $transitionName
                    ? $this->getMachine()->getTransitionById($transitionName)
                    : null,
                $holder
            );

            if ($transition) {
                $this->getMachine()->execute2($transition, $holder);
            }
            $holder->saveModel();
            if ($transition) {
                $transition->callAfterExecute($holder);
            }

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
            $this->logger->log(
                new Message(
                    sprintf(_('Application "%s" saved.'), (string)$holder->getModel()),
                    Message::LVL_SUCCESS
                )
            );
            if ($this->connection->getPdo()->inTransaction()) {
                $this->connection->commit();
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
            /** @var ReferencedId $referencedId */
            foreach ($form->getComponents(true, ReferencedId::class) as $referencedId) {
                $referencedId->rollback();
            }
            $this->connection->rollBack();
            throw new ApplicationHandlerException(_('Error while saving the application.'), 0, $exception);
        }
    }
    private function processData(ArrayHash $values, ?Transition $transition, BaseHolder $holder): ?Transition
    {
        Debugger::log(json_encode((array)$values), 'app-form');
        $newState = null;
        if (isset($values['participant']['status'])) {
            $newState = EventParticipantStatus::tryFrom($values['participant']['status']);
        }

        $processState = $holder->processFormValues($values, $transition);

        $newState = $newState ?? $processState;
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
                        'participant',
                        $newState->label()
                    )
                );
            }
        }
        if (isset($values['participant'])) {
            $holder->data += (array)$values['participant'];
        }
        return $transition;
    }
}
