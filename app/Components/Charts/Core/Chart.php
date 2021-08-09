<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Core;

use Nette\ComponentModel\IComponent;

interface Chart
{
    public function getTitle(): string;

    public function getControl(): IComponent;

    public function getDescription(): ?string;
}
