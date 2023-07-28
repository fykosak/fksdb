<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\NetteORM\TypedSelection;

class PersonProvider implements FilteredDataProvider
{

    private const PLACE = 'place';
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
    public function filterOrgs(ContestModel $contest): void
    {
        $this->searchTable = $this->personService->getTable()
            ->where([
                ':org.contest_id' => $contest->contest_id,
                ':org.since <= ?' => $contest->getCurrentContestYear()->year,
                ':org.until IS NULL OR :org.until <= ?' => $contest->getCurrentContestYear()->year,
            ]);
    }

    /**
     * Prefix search.
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

    public function getItemLabel(int $id): string
    {
        $person = $this->personService->findByPrimary($id);
        return $person->getFullName();
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

    private function getItem(PersonModel $person): array
    {
        $place = null;
        $address = $person->getAddress(PostContactType::tryFrom(PostContactType::DELIVERY));
        if ($address) {
            $place = $address->city;
        }
        return [
            self::LABEL => $person->getFullName(),
            self::VALUE => $person->person_id,
            self::PLACE => $place,
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
