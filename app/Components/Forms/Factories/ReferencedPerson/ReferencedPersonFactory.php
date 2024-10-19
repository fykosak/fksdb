<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use FKSDB\Models\Persons\ResolutionMode;
use FKSDB\Models\Persons\Resolvers\Resolver;
use Nette\DI\Container;
use Nette\SmartObject;

/**
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
class ReferencedPersonFactory
{
    use SmartObject;

    private PersonService $personService;
    private Container $context;

    public function __construct(
        PersonService $personService,
        Container $context
    ) {
        $this->personService = $personService;
        $this->context = $context;
    }

    /**
     * @phpstan-return ReferencedId<PersonModel>
     * @phpstan-param EvaluatedFieldsDefinition $fieldsDefinition
     */
    public function createReferencedPerson(
        array $fieldsDefinition,
        ?ContestYearModel $contestYear,
        string $searchType,
        bool $allowClear,
        Resolver $resolver
    ): ReferencedId {
        $handler = $this->createHandler($contestYear, null);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer(
                $this->context,
                $resolver,
                $contestYear,
                $fieldsDefinition,
                $allowClear
            ),
            $this->personService,
            $handler
        );
    }

    protected function createHandler(
        ?ContestYearModel $contestYear,
        ?ResolutionMode $resolution
    ): ReferencedPersonHandler {
        $handler = new ReferencedPersonHandler(
            $contestYear,
            $resolution ?? ResolutionMode::from(ResolutionMode::EXCEPTION)
        );
        $this->context->callInjects($handler);
        return $handler;
    }
}
