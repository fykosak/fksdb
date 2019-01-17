<?php


namespace FKSDB\Transitions\Operators;


use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statement;

class LogicAnd extends Statement {
    /**
     * @var callable[]
     */
    private $args;

    /**
     * LogicOr constructor.
     * @param array ...$args
     */
    public function __construct(...$args) {
        $this->args = $args;
    }

    /**
     * @param IStateModel|null $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        $res = false;
        foreach ($this->args as $arg) {
            $res = $arg($model) && $res;
            if (!$res) {
                return false;
            }
        }
        return $res;
    }
}
