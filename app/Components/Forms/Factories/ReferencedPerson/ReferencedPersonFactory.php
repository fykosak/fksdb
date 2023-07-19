<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use FKSDB\Models\Persons\ResolutionMode;
use FKSDB\Models\Persons\Resolvers\Resolver;
use Nette\DI\Container;
use Nette\SmartObject;

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

    public function createReferencedPerson(
        array $fieldsDefinition,
        ?ContestYearModel $contestYear,
        string $searchType,
        bool $allowClear,
        Resolver $resolver,
        ?EventModel $event = null
    ): ReferencedId {
        $handler = $this->createHandler($contestYear, null, $event);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer(
                $this->context,
                $resolver,
                $contestYear,
                $fieldsDefinition,
                $event,
                $allowClear
            ),
            $this->personService,
            $handler
        );
    }

    protected function createHandler(
        ?ContestYearModel $contestYear,
        ?ResolutionMode $resolution,
        ?EventModel $event = null
    ): ReferencedPersonHandler {
        $handler = new ReferencedPersonHandler(
            $contestYear,
            $resolution ?? ResolutionMode::tryFrom(ResolutionMode::EXCEPTION)
        );
        if ($event) {
            $handler->setEvent($event);
        }
        $this->context->callInjects($handler);
        return $handler;
    }
}
