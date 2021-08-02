<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

interface ReferencedHandler
{

    public const RESOLUTION_OVERWRITE = 'overwrite';
    public const RESOLUTION_KEEP = 'keep';
    public const RESOLUTION_EXCEPTION = 'exception';

    public function getResolution(): string;

    public function setResolution(string $resolution): void;

    public function update(ActiveRow $model, ArrayHash $values): void;

    public function createFromValues(ArrayHash $values): AbstractModel;

    public function isSecondaryKey(string $field): bool;

    public function findBySecondaryKey(string $field, string $key): ?AbstractModel;
}
