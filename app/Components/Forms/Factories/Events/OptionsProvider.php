<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

interface OptionsProvider
{
    /**
     * @phpstan-return array<string,string>
     */
    public function getOptions(): array;
}
