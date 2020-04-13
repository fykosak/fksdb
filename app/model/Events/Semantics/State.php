<?php

namespace Events\Semantics;

use FKSDB\Expressions\EvaluatedExpression;
use Nette\Application\BadRequestException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class State extends EvaluatedExpression {
    use SmartObject;
    use WithEventTrait;
    /** @var string */
    private $state;

    /**
     * State constructor.
     * @param string $state
     */
    function __construct(string $state) {
        $this->state = $state;
    }

    /**
     * @param array $args
     * @return bool
     * @throws BadRequestException
     */
    public function __invoke(...$args): bool {
        return $this->getHolder($args[0])->getPrimaryHolder()->getModelState() == $this->state;
    }

    /**
     * @return string
     */
    public function __toString() {
        return "state == {$this->state}";
    }

}
