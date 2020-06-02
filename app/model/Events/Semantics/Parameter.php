<?php

namespace FKSDB\Events\Semantics;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Parameter {
    use SmartObject;
    use WithEventTrait;

    private string $parameter;

    /**
     * Parameter constructor.
     * @param $parameter
     */
    public function __construct(string $parameter) {
        $this->parameter = $parameter;
    }

    /**
     * @param array $args
     * @return mixed
     */
    public function __invoke(...$args) {
        return $this->getHolder($args[0])->getParameter($this->parameter);
    }

    public function __toString(): string {
        return "param({$this->parameter})";
    }
}
