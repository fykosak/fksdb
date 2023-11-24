<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Nette\InvalidStateException;

/**
 * @phpstan-type TItem array{label:string,value:int,html:string}
 * @phpstan-implements FilteredDataProvider<TItem>
 */
class SchoolProvider implements FilteredDataProvider
{
    private const LIMIT = 50;

    private SchoolService $schoolService;

    /**
     * School with school_id equal to defaultValue is suggested even when it's not
     * active.
     *
     * @var mixed
     */
    private $defaultValue;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function getFilteredItems(?string $search): array
    {
        $search = trim($search);
        $tokens = preg_split('/[ ,\.]+/', $search);

        $schools = $this->schoolService->getTable();
        foreach ($tokens as $token) { //@phpstan-ignore-line
            $schools->whereOr([
                'school.name_full LIKE concat(\'%\', ?, \'%\')' => $token,
                'school.name_abbrev LIKE concat(\'%\', ?, \'%\')' => $token,
                'address.city LIKE concat(\'%\', ?, \'%\')' => $token,
                'address.country.name LIKE concat(\'%\', ?, \'%\')' => $token,
                'school.name LIKE concat(\'%\', ?, \'%\')' => $token,
            ]);
        }
        // For backwards compatibility consider NULLs active
        if ($this->defaultValue != null) {
            $schools->where('(active IS NULL OR active = 1) OR school_id = ?', $this->defaultValue);
        } else {
            $schools->where('active IS NULL OR active = 1');
        }
        $schools->order('name_abbrev');

        if (count($schools) > self::LIMIT) {
            return [];
        }

        $result = [];
        /** @var SchoolModel $school */
        foreach ($schools as $school) {
            $result[] = $this->getItem($school);
        }
        return $result;
    }

    public function getItemLabel(int $id): array
    {
        $school = $this->schoolService->findByPrimary($id);
        if (!$school) {
            throw new InvalidStateException("Cannot find school with ID '$id'.");
        }
        return $this->getItem($school);
    }

    /**
     * @throws NotImplementedException
     */
    public function getItems(): array
    {
        throw new NotImplementedException();
    }

    /**
     * @phpstan-return TItem
     */
    private function getItem(SchoolModel $school): array
    {
        return [
            'label' => $school->label()->toText(),
            'html' => $school->label()->toHtml(),
            'value' => $school->school_id,
        ];
    }

    /**
     * @param mixed $id
     */
    public function setDefaultValue($id): void
    {
        $this->defaultValue = $id;
    }
}
