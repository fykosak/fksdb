<?php

namespace FKSDB\Model\Events\Model\Holder;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Model\Events\FormAdjustments\IFormAdjustment;
use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Machine\Transition;
use FKSDB\Model\Events\Model\Holder\SecondaryModelStrategies\SecondaryModelStrategy;
use FKSDB\Model\Events\Processing\GenKillProcessing;
use FKSDB\Model\Events\Processing\IProcessing;
use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\ORM\IModel;
use FKSDB\Model\ORM\Models\ModelEvent;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;

/**
 * A bit bloated class.
 *
 * It takes care of data loading/storing and also provides event's metadata.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Holder {

    /** @var IFormAdjustment[] */
    private array $formAdjustments = [];
    /** @var IProcessing[] */
    private array $processings = [];
    /** @var BaseHolder[] */
    private array $baseHolders = [];
    /** @var BaseHolder[] */
    private array $secondaryBaseHolders = [];
    private BaseHolder $primaryHolder;
    private Connection $connection;
    private SecondaryModelStrategy $secondaryModelStrategy;

    public function __construct(Connection $connection) {
        $this->connection = $connection;

        /*
         * This implicit processing is the first. It's not optimal
         * and it may be subject to change.
         */
        $this->processings[] = new GenKillProcessing();
    }

    public function getConnection(): Connection {
        return $this->connection;
    }

    public function setPrimaryHolder(string $name): void {
        $this->primaryHolder = $this->getBaseHolder($name);
        $this->secondaryBaseHolders = array_filter($this->baseHolders, function (BaseHolder $baseHolder): bool {
            return $baseHolder !== $this->primaryHolder;
        });
    }

    public function getPrimaryHolder(): BaseHolder {
        return $this->primaryHolder;
    }

    public function addBaseHolder(BaseHolder $baseHolder): void {
        $baseHolder->setHolder($this);
        $name = $baseHolder->getName();
        $this->baseHolders[$name] = $baseHolder;
    }

    public function addFormAdjustment(IFormAdjustment $formAdjusment): void {
        $this->formAdjustments[] = $formAdjusment;
    }

    public function addProcessing(IProcessing $processing): void {
        $this->processings[] = $processing;
    }

    public function getBaseHolder(string $name): BaseHolder {
        if (!array_key_exists($name, $this->baseHolders)) {
            throw new InvalidArgumentException("Unknown base holder '$name'.");
        }
        return $this->baseHolders[$name];
    }

    /**
     * @return BaseHolder[]
     */
    public function getBaseHolders(): array {
        return $this->baseHolders;
    }

    public function hasBaseHolder(string $name): bool {
        return isset($this->baseHolders[$name]);
    }

    public function getSecondaryModelStrategy(): SecondaryModelStrategy {
        return $this->secondaryModelStrategy;
    }

    public function setSecondaryModelStrategy(SecondaryModelStrategy $secondaryModelStrategy): void {
        $this->secondaryModelStrategy = $secondaryModelStrategy;
    }

    /**
     * @param ModelEvent $event
     * @return static
     * @throws NeonSchemaException
     */
    public function inferEvent(ModelEvent $event): self {
        foreach ($this->getBaseHolders() as $baseHolder) {
            $baseHolder->inferEvent($event);
        }
        return $this;
    }

    public function setModel(?IModel $primaryModel = null, ?array $secondaryModels = null): void {
        foreach ($this->getGroupedSecondaryHolders() as $key => $group) {
            if ($secondaryModels) {
                $this->secondaryModelStrategy->setSecondaryModels($group['holders'], $secondaryModels[$key]);
            } else {
                $this->secondaryModelStrategy->loadSecondaryModels($group['service'], $group['joinOn'], $group['joinTo'], $group['holders'], $primaryModel);
            }
        }
        $this->primaryHolder->setModel($primaryModel);
    }

    public function saveModels(): void {
        /*
         * When deleting, first delete children, then parent.
         */
        if ($this->primaryHolder->getModelState() == \FKSDB\Model\Transitions\Machine\Machine::STATE_TERMINATED) {
            foreach ($this->secondaryBaseHolders as $name => $baseHolder) {
                $baseHolder->saveModel();
            }
            $this->primaryHolder->saveModel();
        } else {
            /*
             * When creating/updating primary model, propagate its PK to referencing secondary models.
             */
            $this->primaryHolder->saveModel();
            $primaryModel = $this->primaryHolder->getModel();

            foreach ($this->getGroupedSecondaryHolders() as $group) {
                $this->secondaryModelStrategy->updateSecondaryModels($group['service'], $group['joinOn'], $group['joinTo'], $group['holders'], $primaryModel);
            }

            foreach ($this->secondaryBaseHolders as $name => $baseHolder) {
                $baseHolder->saveModel();
            }
        }
    }

    /**
     * Apply processings to the values and sets them to the ORM model.
     *
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Transition[] $transitions
     * @param ILogger $logger
     * @param Form|null $form
     * @return string[] machineName => new state
     */
    public function processFormValues(ArrayHash $values, Machine $machine, array $transitions, ILogger $logger, ?Form $form): array {
        $newStates = [];
        foreach ($transitions as $name => $transition) {
            $newStates[$name] = $transition->getTargetState();
        }
        foreach ($this->processings as $processing) {
            $result = $processing->process($newStates, $values, $machine, $this, $logger, $form);
            if ($result) {
                $newStates = array_merge($newStates, $result);
            }
        }

        foreach ($this->baseHolders as $name => $baseHolder) {
            $stateExist = isset($newStates[$name]);
            if ($stateExist) {
                $alive = ($newStates[$name] != \FKSDB\Model\Transitions\Machine\Machine::STATE_TERMINATED);
            } else {
                $alive = true;
            }
            if (isset($values[$name])) {
                $baseHolder->updateModel($values[$name], $alive); // terminated models may not be correctly updated
            }
        }
        return $newStates;
    }

    public function adjustForm(Form $form, Machine $machine): void {
        foreach ($this->formAdjustments as $adjustment) {
            $adjustment->adjust($form, $machine, $this);
        }
    }

    /*
     * Joined data manipulation
     */
    /** @var mixed */
    private $groupedHolders;

    /**
     * Group secondary by service
     * @return BaseHolder[][]|array[] items: joinOn, service, holders
     */
    public function getGroupedSecondaryHolders(): array {
        if ($this->groupedHolders == null) {
            $this->groupedHolders = [];

            foreach ($this->secondaryBaseHolders as $baseHolder) {
                $key = spl_object_hash($baseHolder->getService());
                if (!isset($this->groupedHolders[$key])) {
                    $this->groupedHolders[$key] = [
                        'joinOn' => $baseHolder->getJoinOn(),
                        'joinTo' => $baseHolder->getJoinTo(),
                        'service' => $baseHolder->getService(),
                        'personIds' => $baseHolder->getPersonIdColumns(),
                        'holders' => [],
                    ];
                }
                $this->groupedHolders[$key]['holders'][] = $baseHolder;
                /*
                 * TODO: Here should be consistency check that all
                 * members of the group have same joinOn, joinTo (and maybe others as wellú
                 */
            }
        }

        return $this->groupedHolders;
    }

    /*
     * Parameters
     */

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name) {
        $parts = explode('.', $name, 2);
        if (count($parts) == 1) {
            return $this->primaryHolder->getParameter($name);
        } elseif (isset($this->baseHolders[$parts[0]])) {
            return $this->baseHolders[$parts[0]]->getParameter($parts[1]);
        } else {
            throw new InvalidArgumentException("Invalid parameter '$name' from a base holder.");
        }
    }
}
