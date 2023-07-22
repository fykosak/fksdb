<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Core;

use Fykosak\Utils\UI\Title;

interface Chart
{
    public function getTitle(): Title;

    public function getDescription(): ?string;
}
