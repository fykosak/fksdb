<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;

interface OptionsProvider
{
    /**
     * @phpstan-return array<string,string>
     */
    public function getOptions(Field $field): array;
}
