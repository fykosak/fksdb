<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\SmartObject;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use FKSDB\Models\Persons\SelfResolver;

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
     * @param callable|bool $condition
     */
    public function __construct(Field $field, $condition, SelfResolver $selfResolver, ExpressionEvaluator $evaluator)
    {
        $this->field = $field;
        $this->condition = $condition;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
    }

    public function getResolutionMode(?PersonModel $person): string
    {
        if (!$person) {
            return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
        }
        return ($this->isModifiable($person)) ? ReferencedPersonHandler::RESOLUTION_OVERWRITE
            : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return $this->selfResolver->isModifiable($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }

    public function isVisible(?PersonModel $person): bool
    {
        return $this->selfResolver->isVisible($person) || $this->evaluator->evaluate($this->condition, $this->field);
    }
}
