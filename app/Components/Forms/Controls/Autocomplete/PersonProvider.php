<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedSelection;

/**
 * @phpstan-type TData array{
 * label:string,
 * value:int,
 * place:string|null,
 * }
 * @phpstan-implements FilteredDataProvider<PersonModel,TData>
 */
class PersonProvider implements FilteredDataProvider
{

    private PersonService $personService;
    /**
     * @phpstan-var TypedSelection<PersonModel>
     */
    private TypedSelection $searchTable;

    public function __construct(PersonService $personService)
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

    /**
     * @phpstan-return array<int,TData>
     */
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

    /**
     * @phpstan-return TData
     */
    public function serializeItemId(int $id): array
    {
        $person = $this->personService->findByPrimary($id);
        return $this->serializeItem($person);
    }

    /**
     * @phpstan-return array<int,TData>
     */
    public function getItems(): array
    {
        $persons = $this->searchTable
            ->order('family_name, other_name');

        $result = [];
        /** @var PersonModel $person */
        foreach ($persons as $person) {
            $result[] = $this->serializeItem($person);
        }
        return $result;
    }

    /**
     * @phpstan-return TData
     * @param PersonModel $model
     */
    public function serializeItem(Model $model): array
    {
        $place = null;
        $address = $model->getAddress(PostContactType::from(PostContactType::DELIVERY));
        if ($address) {
            $place = $address->city;
        }
        return [
            'label' => $model->getFullName(),
            'value' => $model->person_id,
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
