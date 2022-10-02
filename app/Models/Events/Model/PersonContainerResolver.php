<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\SmartObject;
use FKSDB\Models\Persons\Resolvers\Resolver;
use FKSDB\Models\Persons\Resolvers\SelfResolver;

class PersonContainerResolver implements Resolver
{
    use SmartObject;

    private Field $field;
    /** @var callable */
    private $modifiableCondition;
    /** @var callable */
    private $visibleCondition;
    private SelfResolver $selfResolver;
    private ExpressionEvaluator $evaluator;

    /**
     * PersonContainerResolver constructor.
     * @param callable|bool $modifiableCondition
     * @param callable|bool $visibleCondition
     */
    public function __construct(
        Field $field,
        $modifiableCondition,
        $visibleCondition,
        SelfResolver $selfResolver,
        ExpressionEvaluator $evaluator
    ) {
        $this->field = $field;
        $this->modifiableCondition = $modifiableCondition;
        $this->visibleCondition = $visibleCondition;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
    }

    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        if (!$person) {
            return ResolutionMode::tryFrom(ResolutionMode::EXCEPTION);
        }
        return ($this->isModifiable($person)) ? ResolutionMode::tryFrom(ResolutionMode::OVERWRITE)
            : ResolutionMode::tryFrom(ResolutionMode::EXCEPTION);
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return $this->selfResolver->isModifiable($person) ||
            $this->evaluator->evaluate($this->modifiableCondition, $this->field);
    }

    public function isVisible(?PersonModel $person): bool
    {
        return $this->selfResolver->isVisible($person) ||
            $this->evaluator->evaluate($this->visibleCondition, $this->field);
    }
}
