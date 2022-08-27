<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\FormAdjustments\FormAdjustment;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\Events\Processing\GenKillProcessing;
use FKSDB\Models\Events\Processing\Processing;
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
    public BaseHolder $primaryHolder;

    public function __construct()
    {
        /*
         * This implicit processing is the first. It's not optimal
         * and it may be subject to change.
         */
        $this->processings[] = new GenKillProcessing();
    }

    public function addBaseHolder(BaseHolder $baseHolder): void
    {
        $this->primaryHolder = $baseHolder;
        $this->primaryHolder->setHolder($this);
    }

    public function addFormAdjustment(FormAdjustment $formAdjustment): void
    {
        $this->formAdjustments[] = $formAdjustment;
    }

    public function addProcessing(Processing $processing): void
    {
        $this->processings[] = $processing;
    }

    /**
     * @return static
     * @throws NeonSchemaException
     */
    public function inferEvent(EventModel $event): self
    {
        $this->primaryHolder->inferEvent($event);
        return $this;
    }

    public function setModel(?Model $primaryModel = null): void
    {
        $this->primaryHolder->setModel($primaryModel);
    }

    public function saveModels(): void
    {
        $this->primaryHolder->saveModel();
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
        } else {
            throw new InvalidArgumentException("Invalid parameter '$name' from a base holder.");
        }
    }
}
