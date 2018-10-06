<?php

namespace Events\Model;

use Events\Model\Holder\Field;
use FKSDB\ORM\ModelPerson;
use Nette\Object;
use Persons\IModifiabilityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandler;
use Persons\SelfResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonContainerResolver extends Object implements IVisibilityResolver, IModifiabilityResolver {

    /**
     * @var Field
     */
    private $field;

    /**
     * @var mixed
     */
    private $condition;

    /**
     * @var SelfResolver
     */
    private $selfResolver;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    function __construct(Field $field, $condition, SelfResolver $selfResolver, ExpressionEvaluator $evaluator) {
        $this->field = $field;
        $this->condition = $condition;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
    }

    public function getResolutionMode(ModelPerson $person) {
        return (!$person->isNew() && $this->isModifiable($person)) ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person) {
        return $this->selfResolver->isModifiable($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }

    public function isVisible(ModelPerson $person) {
        return $this->selfResolver->isVisible($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }

//put your code here
}
