<?php

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\SmartObject;
use FKSDB\Models\Persons\IModifiabilityResolver;
use FKSDB\Models\Persons\IVisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use FKSDB\Models\Persons\SelfResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonContainerResolver implements IVisibilityResolver, IModifiabilityResolver {

    use SmartObject;

    private Field $field;

    /** @var mixed */
    private $condition;

    private SelfResolver $selfResolver;

    private ExpressionEvaluator $evaluator;

    /**
     * PersonContainerResolver constructor.
     * @param Field $field
     * @param mixed $condition
     * @param SelfResolver $selfResolver
     * @param ExpressionEvaluator $evaluator
     */
    public function __construct(Field $field, $condition, SelfResolver $selfResolver, ExpressionEvaluator $evaluator) {
        $this->field = $field;
        $this->condition = $condition;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
    }

    public function getResolutionMode(ModelPerson $person): string {
        return (!$person->isNew() && $this->isModifiable($person)) ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person): bool {
        return $this->selfResolver->isModifiable($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }

    public function isVisible(ModelPerson $person): bool {
        return $this->selfResolver->isVisible($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }
}
