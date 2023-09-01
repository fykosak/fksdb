<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Nette\InvalidStateException;

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

    /**
     * Prefix search.
     */
    public function getFilteredItems(?string $search): array
    {
        $search = trim($search);
        $tokens = preg_split('/[ ,\.]+/', $search);

        $schools = $this->schoolService->getTable();
        foreach ($tokens as $token) { //@phpstan-ignore-line
            $schools->where(
                'name_full LIKE concat(\'%\', ?, \'%\') OR name_abbrev LIKE concat(\'%\', ?, \'%\')',
                $token,
                $token
            );
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

    public function getItemLabel(int $id): string
    {
        $school = $this->schoolService->findByPrimary($id);
        if (!$school) {
            throw new InvalidStateException("Cannot find school with ID '$id'.");
        }
        return $school->name_abbrev;
    }

    /**
     * @throws NotImplementedException
     */
    public function getItems(): array
    {
        throw new NotImplementedException();
    }

    /**
     * @phpstan-return array{label:string,value:int}
     */
    private function getItem(SchoolModel $school): array
    {
        return [
            'label' => $school->name_abbrev,
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
