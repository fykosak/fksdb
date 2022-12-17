<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use Fykosak\NetteORM\Model;

abstract class ReferencedHandler
{
    protected ResolutionMode $resolution;

    final public function getResolution(): ResolutionMode
    {
        return $this->resolution;
    }

    final public function setResolution(ResolutionMode $resolution): void
    {
        $this->resolution = $resolution;
    }

    abstract public function store(array $values, ?Model $model = null): ?Model;

    protected function findModelConflicts(Model $model, array $values, ?string $subKey): array
    {
        foreach ($values as $key => $value) {
            if (isset($model[$key]) && $model[$key] != $value) {
                switch ($this->resolution->value) {
                    case ResolutionMode::EXCEPTION:
                        throw new ModelDataConflictException(
                            $subKey ? [$subKey => [$key => $value]] : [$key => $value]
                        );
                    case ResolutionMode::KEEP:
                        unset($values[$key]);
                        break;
                    case ResolutionMode::OVERWRITE:
                        break;
                }
            }
        }
        return $values;
    }
}
