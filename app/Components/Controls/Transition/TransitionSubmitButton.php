<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionException;
use Nette\Forms\Controls\SubmitButton;

/**
 * @phpstan-template THolder of ModelHolder
 */
class TransitionSubmitButton extends SubmitButton
{
    /** @var Transition<THolder> */
    private Transition $transition;
    /** @var THolder|null */
    private ?ModelHolder $holder;

    /**
     * @param Transition<THolder> $transition
     * @param THolder|null $holder
     */
    public function __construct(Transition $transition, ?ModelHolder $holder)
    {
        parent::__construct($transition->label->toHtml());
        $this->transition = $transition;
        $this->holder = $holder;
        if (!$this->transition->validation) {
            $this->setValidationScope([]);
        }
        if ($this->holder && !$this->transition->canExecute($this->holder)) {
            $this->disabled = true;
        }
        $this->getControlPrototype()->addAttributes(
            ['class' => 'btn btn-outline-' . $transition->behaviorType->value]
        );
        $this->onClick[] = function (): void {
            if ($this->holder && !$this->transition->canExecute($this->holder)) {
                throw new UnavailableTransitionException($this->transition, $this->holder);
            }
        };
    }
}
