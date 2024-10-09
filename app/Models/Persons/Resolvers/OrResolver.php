<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;

class OrResolver implements Resolver
{
    /** @var Resolver[] */
    private array $resolvers;

    /**
     * @param Resolver[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function isVisible(?PersonModel $person): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isVisible($person)) {
                return true;
            }
        }
        return false;
    }

    public function isModifiable(?PersonModel $person): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isModifiable($person)) {
                return true;
            }
        }
        return false;
    }

    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        foreach ($this->resolvers as $resolver) {
            $mode = $resolver->getResolutionMode($person);
            if ($mode->value === ResolutionMode::OVERWRITE) {
                return $mode;
            }
        }
        return ResolutionMode::from(ResolutionMode::EXCEPTION);
    }
}
