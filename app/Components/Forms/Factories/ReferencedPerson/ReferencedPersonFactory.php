<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandlerFactory;
use FKSDB\Models\Persons\VisibilityResolver;
use Nette\DI\Container;
use Nette\SmartObject;

class ReferencedPersonFactory
{
    use SmartObject;

    private PersonService $personService;
    private ReferencedPersonHandlerFactory $referencedPersonHandlerFactory;
    private Container $context;

    public function __construct(
        PersonService $personService,
        ReferencedPersonHandlerFactory $referencedPersonHandlerFactory,
        Container $context
    ) {
        $this->personService = $personService;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
        $this->context = $context;
    }

    public function createReferencedPerson(
        array $fieldsDefinition,
        ContestYearModel $contestYear,
        string $searchType,
        bool $allowClear,
        ModifiabilityResolver $modifiabilityResolver,
        VisibilityResolver $visibilityResolver,
        ?EventModel $event = null
    ): ReferencedId {
        $handler = $this->referencedPersonHandlerFactory->create($contestYear, null, $event);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer(
                $this->context,
                $modifiabilityResolver,
                $visibilityResolver,
                $contestYear,
                $fieldsDefinition,
                $event,
                $allowClear
            ),
            $this->personService,
            $handler
        );
    }
}
