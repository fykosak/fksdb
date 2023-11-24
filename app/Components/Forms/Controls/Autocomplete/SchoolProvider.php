<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidStateException;

/**
 * @phpstan-type TData array{label:string,value:int,iso:string,city:string,country:string}
 * @phpstan-implements FilteredDataProvider<SchoolModel,TData>
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
            $result[] = $this->serializeItem($school);
        }
        return $result;
    }

    /**
     * @phpstan-return TData
     */
    public function serializeItemId(int $id): array
    {
        $school = $this->schoolService->findByPrimary($id);
        if (!$school) {
            throw new InvalidStateException("Cannot find school with ID '$id'.");
        }
        return $this->serializeItem($school);
    }

    /**
     * @return never
     * @throws NotImplementedException
     */
    public function getItems(): array
    {
        throw new NotImplementedException();
    }

    /**
     * @phpstan-return TData
     * @param SchoolModel $model
     */
    public function serializeItem(Model $model): array
    {
        return [
            'label' => $model->name_abbrev,
            'iso' => strtolower($model->address->country->alpha_2),
            'country' => $model->address->country->name,
            'city' => $model->address->city,
            'value' => $model->school_id,
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
