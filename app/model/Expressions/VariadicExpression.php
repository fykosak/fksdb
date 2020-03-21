<?php

namespace FKSDB\Expressions;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class VariadicExpression extends EvaluatedExpression {

    private $arguments;

    public function __construct() {
        $this->arguments = func_get_args();
    }

    /**
     * @return mixed
     */
    public function __invoke() {
        $args = func_get_args();
        return $this->evaluate($args);
    }

    /**
     * @param $args
     * @return mixed
     */
    abstract protected function evaluate($args);

    /**
     * @return mixed
     */
    abstract protected function getInfix();

    /**
     * @param $index
     * @param $args
     * @return mixed
     */
    protected function evalArgAt($index, $args) {
        return $this->evalArg($this->arguments[$index], $args);
    }

    /**
     * @param $index
     * @return mixed
     */
    protected function getArg($index) {
        return $this->arguments[$index];
    }

    /**
     * @return int
     */
    public function getArity() {
        return count($this->arguments);
    }

    /**
     * @return string
     */
    public function __toString() {
        $terms = [];
        foreach ($this->arguments as $arg) {
            $terms[] = (string) $arg;
        }
        $result = implode(' ' . $this->getInfix() . ' ', $terms);
        if (count($terms) > 1) {
            $result = "($result)";
        }
        return $result;
    }

}
