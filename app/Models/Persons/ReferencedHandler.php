<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use Fykosak\NetteORM\Model;

abstract class ReferencedHandler
{

    public const RESOLUTION_OVERWRITE = 'overwrite';
    public const RESOLUTION_KEEP = 'keep';
    public const RESOLUTION_EXCEPTION = 'exception';

    protected string $resolution;

    final public function getResolution(): string
    {
        return $this->resolution;
    }

    final public function setResolution(string $resolution): void
    {
        $this->resolution = $resolution;
    }

    abstract public function store(array $values, ?Model $model = null): Model;

    protected function findModelConflicts(Model $model, array $values, ?string $subKey): array
    {
        foreach ($values as $key => $value) {
            if (isset($model[$key]) && $model[$key] != $value) {
                switch ($this->resolution) {
                    case self::RESOLUTION_EXCEPTION:
                        throw new ModelDataConflictException(
                            $subKey ? [$subKey => [$key => $value]] : [$key => $value]
                        );
                    case self::RESOLUTION_KEEP:
                        unset($values[$key]);
                        break;
                    case self::RESOLUTION_OVERWRITE:
                        break;
                }
            }
        }
        return $values;
    }
}
