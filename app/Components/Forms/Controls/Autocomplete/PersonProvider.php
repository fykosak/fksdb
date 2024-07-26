<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\DI\Container;

/**
 * @phpstan-type TItem array{
 *     label:string,
 *     value:int,
 *     place:string|null,
 * }
 * @phpstan-implements FilteredDataProvider<TItem>
 */
class PersonProvider implements FilteredDataProvider
{

    private PersonService $personService;
    /**
     * @phpstan-var TypedSelection<PersonModel>
     */
    private TypedSelection $searchTable;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(PersonService $personService): void
    {
        $this->personService = $personService;
        $this->searchTable = $this->personService->getTable();
    }

    /**
     * Syntactic sugar, should be solved more generally.
     */
    public function filterOrganizers(ContestModel $contest): void
    {
        $this->searchTable = $this->personService->getTable()
            ->where([
                ':org.contest_id' => $contest->contest_id,
                ':org.since <= ?' => $contest->getCurrentContestYear()->year,
                ':org.until IS NULL OR :org.until <= ?' => $contest->getCurrentContestYear()->year,
            ]);
    }

    public function getFilteredItems(?string $search): array
    {
        $search = trim($search);
        $search = str_replace(' ', '', $search);
        $this->searchTable
            ->where(
                'family_name LIKE concat(?, \'%\') OR other_name LIKE concat(?, \'%\') 
                OR concat(other_name, family_name) LIKE concat(?,  \'%\')',
                $search,
                $search,
                $search
            );
        return $this->getItems();
    }

    public function getItemLabel(int $id): array
    {
        $person = $this->personService->findByPrimary($id);
        return $this->getItem($person);
    }

    public function getItems(): array
    {
        $persons = $this->searchTable
            ->order('family_name, other_name');

        $result = [];
        /** @var PersonModel $person */
        foreach ($persons as $person) {
            $result[] = $this->getItem($person);
        }
        return $result;
    }

    /**
     * @phpstan-return array{
     *     label:string,
     *     value:int,
     *     place:string|null,
     * }
     */
    private function getItem(PersonModel $person): array
    {
        $place = null;
        $address = $person->getAddress(PostContactType::Delivery);
        if ($address) {
            $place = $address->city;
        }
        return [
            'label' => $person->getFullName(),
            'value' => $person->person_id,
            'place' => $place,
        ];
    }

    /**
     * @param mixed $id
     */
    public function setDefaultValue($id): void
    {
        /* intentionally blank */
    }
}
