<?php

namespace FKSDB\Components\Charts;

use Nette\ComponentModel\IComponent;

interface Chart {

    public function getTitle(): string;

    public function getControl(): IComponent;

    public function getDescription(): ?string;
}
