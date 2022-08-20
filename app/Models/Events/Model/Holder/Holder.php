<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\FormAdjustments\FormAdjustment;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\Events\Model\Holder\SecondaryModelStrategies\SecondaryModelStrategy;
use FKSDB\Models\Events\Processing\GenKillProcessing;
use FKSDB\Models\Events\Processing\Processing;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;

/**
 * A bit bloated class.
 *
 * It takes care of data loading/storing and also provides event's metadata.
 */
class Holder
{

    /** @var FormAdjustment[] */
    private array $formAdjustments = [];

    /** @var Processing[] */
    private array $processings = [];
    /** @var BaseHolder[] */
    private array $baseHolders = [];
    /** @var BaseHolder[] */
    private array $secondaryBaseHolders = [];
    public BaseHolder $primaryHolder;
    private SecondaryModelStrategy $secondaryModelStrategy;

    public function __construct()
    {
        /*
         * This implicit processing is the first. It's not optimal
         * and it may be subject to change.
         */
        $this->processings[] = new GenKillProcessing();
    }

    public function setPrimaryHolder(string $name): void
    {
        $this->primaryHolder = $this->getBaseHolder($name);
        $this->secondaryBaseHolders = array_filter(
            $this->baseHolders,
            fn(BaseHolder $baseHolder): bool => $baseHolder !== $this->primaryHolder
        );
    }

    public function addBaseHolder(BaseHolder $baseHolder): void
    {
        $baseHolder->setHolder($this);
        $name = $baseHolder->name;
        $this->baseHolders[$name] = $baseHolder;
    }

    public function addFormAdjustment(FormAdjustment $formAdjustment): void
    {
        $this->formAdjustments[] = $formAdjustment;
    }

    public function addProcessing(Processing $processing): void
    {
        $this->processings[] = $processing;
    }

    public function getBaseHolder(string $name): BaseHolder
    {
        if (!isset($this->baseHolders[$name])) {
            throw new InvalidArgumentException("Unknown base holder '$name'.");
        }
        return $this->baseHolders[$name];
    }

    /**
     * @return BaseHolder[]
     */
    public function getBaseHolders(): array
    {
        return $this->baseHolders;
    }

    public function hasBaseHolder(string $name): bool
    {
        return isset($this->baseHolders[$name]);
    }

    public function setSecondaryModelStrategy(SecondaryModelStrategy $secondaryModelStrategy): void
    {
        $this->secondaryModelStrategy = $secondaryModelStrategy;
    }

    /**
     * @return static
     * @throws NeonSchemaException
     */
    public function inferEvent(EventModel $event): self
    {
        foreach ($this->getBaseHolders() as $baseHolder) {
            $baseHolder->inferEvent($event);
        }
        return $this;
    }

    public function setModel(?Model $primaryModel = null, ?array $secondaryModels = null): void
    {
        foreach ($this->getGroupedSecondaryHolders() as $key => $group) {
            if ($secondaryModels) {
                $this->secondaryModelStrategy->setSecondaryModels($group['holders'], $secondaryModels[$key]);
            } else {
                $this->secondaryModelStrategy->loadSecondaryModels(
                    $group['service'],
                    $group['joinOn'],
                    $group['joinTo'],
                    $group['holders'],
                    $primaryModel
                );
            }
        }
        $this->primaryHolder->setModel($primaryModel);
    }

    public function saveModels(): void
    {
        /*
         * When deleting, first delete children, then parent.
         */
        if ($this->primaryHolder->getModelState() == AbstractMachine::STATE_TERMINATED) {
            foreach ($this->secondaryBaseHolders as $baseHolder) {
                $baseHolder->saveModel();
            }
            $this->primaryHolder->saveModel();
        } else {
            /*
             * When creating/updating primary model, propagate its PK to referencing secondary models.
             */
            $this->primaryHolder->saveModel();
            $primaryModel = $this->primaryHolder->getModel2();

            foreach ($this->getGroupedSecondaryHolders() as $group) {
                $this->secondaryModelStrategy->updateSecondaryModels(
                    $group['service'],
                    $group['joinOn'],
                    $group['joinTo'],
                    $group['holders'],
                    $primaryModel
                );
            }

            foreach ($this->secondaryBaseHolders as $baseHolder) {
                $baseHolder->saveModel();
            }
        }
    }

    /**
     * Apply processings to the values and sets them to the ORM model.
     *
     * @param Transition[] $transitions
     * @return string[] machineName => new state
     */
    public function processFormValues(
        ArrayHash $values,
        Machine $machine,
        array $transitions,
        Logger $logger,
        ?Form $form
    ): array {
        $newStates = [];
        foreach ($transitions as $name => $transition) {
            $newStates[$name] = $transition->target;
        }
        foreach ($this->processings as $processing) {
            $result = $processing->process($newStates, $values, $machine, $this, $logger, $form);
            if ($result) {
                $newStates = array_merge($newStates, $result);
            }
        }

        return $newStates;
    }

    public function adjustForm(Form $form): void
    {
        foreach ($this->formAdjustments as $adjustment) {
            $adjustment->adjust($form, $this);
        }
    }

    /*
     * Joined data manipulation
     */
    private array $groupedHolders;

    /**
     * Group secondary by service
     * @return BaseHolder[][]|array[] items: joinOn, service, holders
     */
    public function getGroupedSecondaryHolders(): array
    {
        if (!isset($this->groupedHolders)) {
            $this->groupedHolders = [];

            foreach ($this->secondaryBaseHolders as $baseHolder) {
                $key = spl_object_hash($baseHolder->getService());
                if (!isset($this->groupedHolders[$key])) {
                    $this->groupedHolders[$key] = [
                        'joinOn' => $baseHolder->joinOn,
                        'joinTo' => $baseHolder->joinTo,
                        'service' => $baseHolder->getService(),
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
     * @return mixed
     */
    public function getParameter(string $name)
    {
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
