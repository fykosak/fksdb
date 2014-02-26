<?php

namespace FKS\Expressions\Predicates;

use Nette\DateTime;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class After extends Object {

    /** @var DateTime */
    private $datetime;

    function __construct(DateTime $datetime) {
        $this->datetime = $datetime;
    }

    public function __invoke() {
        return $this->datetime->getTimestamp() <= time();
    }

    public function __toString() {
        return "now >= {$this->datetime}";
    }

}
