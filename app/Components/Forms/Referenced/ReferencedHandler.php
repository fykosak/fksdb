<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Referenced;

use Fykosak\NetteORM\Model;

interface ReferencedHandler
{

    public const RESOLUTION_OVERWRITE = 'overwrite';
    public const RESOLUTION_KEEP = 'keep';
    public const RESOLUTION_EXCEPTION = 'exception';

    public function getResolution(): string;

    public function setResolution(string $resolution): void;

    public function update(Model $model, array $values): void;

    public function createFromValues(array $values): Model;
}
