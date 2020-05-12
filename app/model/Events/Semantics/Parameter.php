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

    private $parameter;

    /**
     * Parameter constructor.
     * @param $parameter
     */
    function __construct($parameter) {
        $this->parameter = $parameter;
    }

    /**
     * @param array $args
     * @return mixed
     */
    public function __invoke(...$args) {
        return $this->getHolder($args[0])->getParameter($this->parameter);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "param({$this->parameter})";
    }

}
