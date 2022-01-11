<?php

namespace FKSDB\Models\Persons;

use Fykosak\NetteORM\AbstractModel;

interface ReferencedHandler
{

    public const RESOLUTION_OVERWRITE = 'overwrite';
    public const RESOLUTION_KEEP = 'keep';
    public const RESOLUTION_EXCEPTION = 'exception';

    public function getResolution(): string;

    public function setResolution(string $resolution): void;

    public function update(AbstractModel $model, array $values): void;

    public function createFromValues(array $values): AbstractModel;

    public function findBySecondaryKey(string $key): ?AbstractModel;
}
