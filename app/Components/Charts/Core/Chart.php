<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Charts\Core;

interface Chart
{
    public function getTitle(): string;

    public function getDescription(): ?string;
}
