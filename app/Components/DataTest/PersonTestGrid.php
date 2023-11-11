<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\TypedSelection;

/**
 * @phpstan-extends BaseGrid<PersonModel,array{}>
 */
class PersonTestGrid extends BaseGrid
{
    /** @use TestGridTrait<PersonModel> */
    use TestGridTrait;

    private PersonService $personService;

    final public function injectPrimary(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /**
     * @phpstan-return TypedSelection<PersonModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->personService->getTable();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addSimpleReferencedColumns(['@person.person_id', '@person.full_name']);
        $this->addLink('person.detail');
        $this->addTests($this->dataTestFactory->getPersonTests());
    }
}
