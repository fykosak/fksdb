<?php

namespace FKSDB\Components\Forms\Controls;

use InvalidArgumentException;
use ModelContestant;
use ModelSubmit;
use Nette\DateTime;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use ServiceSubmit;
use Traversable;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantSubmits extends BaseControl {

    /**
     * @var Traversable|array of ModelTask
     */
    private $tasks;

    /**
     * @var string
     */
    private $rawValue;

    /**
     * @var ServiceSubmit
     */
    private $submitService;

    /**
     * @var ModelContestant
     */
    private $contestant;

    /**
     * 
     * @param type $tasks
     * @param \FKSDB\Components\Forms\Controls\ServiceSubmit $submitService
     * @param string|null $label
     */
    function __construct($tasks, ModelContestant $contestant, ServiceSubmit $submitService, $label = null) {
        parent::__construct($label);

        $this->tasks = $tasks;
        $this->submitService = $submitService;
        $this->contestant = $contestant;

        $this->setValue(null);
    }

    /**
     * @return   Html
     */
    public function getControl() {
        $control = parent::getControl();

        $control->value = $this->rawValue;
        return $control;
    }

    /**
     * 
     * @param array|Traversable|string $value of ModelTask
     * @return \FKSDB\Components\Forms\Controls\ContestantSubmits
     * @throws InvalidArgumentException
     */
    public function setValue($value) {
        if (!$value) {
            $this->rawValue = $this->serializeValue(array());
            $this->value = $this->deserializeValue($this->rawValue);
        } else if (is_string($value)) {
            $this->rawValue = $value;
            $this->value = $this->deserializeValue($value);
        } else {
            $this->rawValue = $this->serializeValue($value);
            $this->value = $value;
        }

        return $this;
    }

    private function serializeValue($value) {
        $values = array_fill_keys(range(1, count($this->tasks)), null);

        foreach ($value as $submit) {
            if (!$submit) {
                continue;
            }

            $tasknr = $submit->getTask()->tasknr;

            if (!array_key_exists($tasknr, $values) || $values[$tasknr] !== null) {
                throw new InvalidArgumentException('Unexpected submits for element with such specified tasks.');
            }
            $values[(int) $tasknr] = $this->serializeSubmit($submit);
        }

        return json_encode($values);
    }

    private function deserializeValue($value) {
        $value = json_decode($value, true);

        $result = array();

        foreach ($value as $tasknr => $serializedSubmit) {
            if (!$serializedSubmit) {
                continue;
            }

            $result[] = $this->deserializeSubmit($serializedSubmit, $tasknr);
        }

        return $result;
    }

    private function serializeSubmit(ModelSubmit $submit) {
        $data = $submit->toArray();
        $data['submitted_on'] = $data['submitted_on'] ? $data['submitted_on']->format(DateTime::ISO8601) : null;
        return $data;
    }

    private function deserializeSubmit($data, $tasknr) {
        if (!$data) {
            return null; //TODO consider this case
        }

        unset($data['submit_id']); // security
        $data['ct_id'] = $this->contestant->ct_id; // security
        $data['submitted_on'] = $data['submitted_on'] ? DateTime::createFromFormat(DateTime::ISO8601, $data['submitted_on']) : null;
        $data['tasknr'] = $tasknr;

        $ctId = $data['ct_id'];
        $taskId = $data['task_id'];

        $submit = $this->submitService->findByContestant($ctId, $taskId);
        if (!$submit) {
            $submit = $this->submitService->createNew();
        }

        $this->submitService->updateModel($submit, $data);
        return $submit;
    }

//    /**
//     * Does user enter anything? (the value doesn't have to be valid)
//     *
//     * @author   Jan Tvrdík
//     * @param    DatePicker
//     * @return   bool
//     */
//    public static function validateFilled(IControl $control) {
//        if (!$control instanceof self)
//            throw new InvalidStateException('Unable to validate ' . get_class($control) . ' instance.');
//        $rawValue = $control->rawValue;
//        return !empty($rawValue);
//    }
//
//    /**
//     * Is entered value valid? (empty value is also valid!)
//     *
//     * @author   Jan Tvrdík
//     * @param    DatePicker
//     * @return   bool
//     */
//    public static function validateValid(IControl $control) {
//        if (!$control instanceof self)
//            throw new InvalidStateException('Unable to validate ' . get_class($control) . ' instance.');
//        $value = $control->value;
//        return (empty($control->rawValue) || $value instanceof DateTime2);
//    }
//
//    /**
//     * Is entered values within allowed range?
//     *
//     * @author   Jan Tvrdík, David Grudl
//     * @param    DatePicker
//     * @param    array             0 => minDate, 1 => maxDate
//     * @return   bool
//     */
//    public static function validateRange(IControl $control, $range) {
//        return Validators::isInRange($control->getValue(), $range);
//    }
//
//    /**
//     * Finds minimum and maximum allowed dates.
//     *
//     * @author   Jan Tvrdík
//     * @param    Rules
//     * @return   array             0 => DateTime|NULL $minDate, 1 => DateTime|NULL $maxDate
//     */
//    private function extractRangeRule(Rules $rules) {
//        $controlMin = $controlMax = NULL;
//        foreach ($rules as $rule) {
//            if ($rule->type === Rule::VALIDATOR) {
//                if ($rule->operation === Form::RANGE && !$rule->isNegative) {
//                    $ruleMinMax = $rule->arg;
//                }
//            } elseif ($rule->type === Rule::CONDITION) {
//                if ($rule->operation === Form::FILLED && !$rule->isNegative && $rule->control === $this) {
//                    $ruleMinMax = $this->extractRangeRule($rule->subRules);
//                }
//            }
//
//            if (isset($ruleMinMax)) {
//                list($ruleMin, $ruleMax) = $ruleMinMax;
//                if ($ruleMin !== NULL && ($controlMin === NULL || $ruleMin > $controlMin))
//                    $controlMin = $ruleMin;
//                if ($ruleMax !== NULL && ($controlMax === NULL || $ruleMax < $controlMax))
//                    $controlMax = $ruleMax;
//                $ruleMinMax = NULL;
//            }
//        }
//        return array($controlMin, $controlMax);
//    }
}
