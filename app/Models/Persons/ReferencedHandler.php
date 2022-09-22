<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use Fykosak\NetteORM\Model;

interface ReferencedHandler
{
    public function setResolution(ResolutionMode $resolution): void;

    public function update(Model $model, array $values): void;

    public function createFromValues(array $values): Model;
}
