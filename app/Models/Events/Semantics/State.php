<?php

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Expressions\EvaluatedExpression;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class State extends EvaluatedExpression {
    use SmartObject;
    use WithEventTrait;

    private string $state;

    public function __construct(string $state) {
        $this->state = $state;
    }

    public function __invoke(...$args): bool {
        return $this->getHolder($args[0])->getPrimaryHolder()->getModelState() == $this->state;
    }

    public function __toString(): string {
        return "state == {$this->state}";
    }
}
