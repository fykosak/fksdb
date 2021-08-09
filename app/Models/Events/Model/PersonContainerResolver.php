<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\ReferencedHandler;
use FKSDB\Models\Persons\SelfResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use Nette\SmartObject;

class PersonContainerResolver implements VisibilityResolver, ModifiabilityResolver
{
    use SmartObject;

    private Field $field;

    /** @var callable */
    private $condition;

    private SelfResolver $selfResolver;

    private ExpressionEvaluator $evaluator;

    /**
     * PersonContainerResolver constructor.
     * @param Field $field
     * @param callable|bool $condition
     * @param SelfResolver $selfResolver
     * @param ExpressionEvaluator $evaluator
     */
    public function __construct(Field $field, $condition, SelfResolver $selfResolver, ExpressionEvaluator $evaluator)
    {
        $this->field = $field;
        $this->condition = $condition;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
    }

    public function getResolutionMode(?ModelPerson $person): string
    {
        if (!$person) {
            return ReferencedHandler::RESOLUTION_EXCEPTION;
        }
        return ($this->isModifiable(
            $person
        )) ? ReferencedHandler::RESOLUTION_OVERWRITE : ReferencedHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?ModelPerson $person): bool
    {
        return $this->selfResolver->isModifiable($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }

    public function isVisible(?ModelPerson $person): bool
    {
        return $this->selfResolver->isVisible($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }
}
