<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use Fykosak\NetteORM\Model\Model;

class ModelContainer extends ContainerWithOptions
{
    /**
     * @phpstan-param Model|iterable<string|int,mixed> $data
     * @return static
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof Model) {
            $data = $data->toArray();
        }
        return parent::setValues($data, $erase);
    }
}
