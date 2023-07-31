<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use FKSDB\Models\Persons\Resolvers\Resolver;
use FKSDB\Models\Persons\Resolvers\SelfResolver;
use Nette\SmartObject;

class PersonContainerResolver implements Resolver
{
    use SmartObject;

    private Field $field;
    /** @phpstan-var callable(BaseHolder):bool|bool */
    private $modifiableCondition;
    /** @phpstan-var callable(BaseHolder):bool|bool */
    private $visibleCondition;
    private SelfResolver $selfResolver;

    /**
     * @param callable(BaseHolder):bool|bool $modifiableCondition
     * @param callable(BaseHolder):bool|bool $visibleCondition
     */
    public function __construct(
        Field $field,
        $modifiableCondition,
        $visibleCondition,
        SelfResolver $selfResolver
    ) {
        $this->field = $field;
        $this->modifiableCondition = $modifiableCondition;
        $this->visibleCondition = $visibleCondition;
        $this->selfResolver = $selfResolver;
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
            (is_callable($this->modifiableCondition)
                ? ($this->modifiableCondition)($this->field->holder)
                : $this->modifiableCondition);
    }

    public function isVisible(?PersonModel $person): bool
    {
        return $this->selfResolver->isVisible($person) ||
            (is_callable($this->visibleCondition)
                ? ($this->visibleCondition)($this->field->holder)
                : $this->visibleCondition);
    }
}
