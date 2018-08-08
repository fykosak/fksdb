<?php

namespace Events\Model;

use Events\Model\Holder\Field;
use ModelPerson;
use Nette\Object;
use Persons\IResolver;
use Persons\ReferencedPersonHandler;
use Persons\SelfResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonContainerResolver extends Object implements IResolver {

    /**
     * @var Field
     */
    private $field;

    /**
     * @var mixed
     */
    private $modifiableCondition;
    private $visibleCondition;

    /**
     * @var SelfResolver
     */
    private $selfResolver;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    function __construct(Field $field, $modifiableCondition, $visibleCondition, SelfResolver $selfResolver, ExpressionEvaluator $evaluator) {
        $this->field = $field;
        $this->modifiableCondition = $modifiableCondition;
        $this->visibleCondition = $visibleCondition;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
    }

    public function getResolutionMode(ModelPerson $person) {
        return (!$person->isNew() && $this->isModifiable($person)) ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person) {
        return $this->selfResolver->isModifiable($person) || $this->evaluator->evaluate($this->modifiableCondition, $this->field);
    }

    public function isVisible(ModelPerson $person) {
        return $this->selfResolver->isVisible($person) || $this->evaluator->evaluate($this->visibleCondition, $this->field);
    }

//put your code here
}
