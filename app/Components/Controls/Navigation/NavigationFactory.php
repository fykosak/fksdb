<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use Nette\SmartObject;

class NavigationFactory
{
    use SmartObject;

    private array $structure;

    public function setStructure(array $structure): void
    {
        $this->structure = $structure;
    }

    public function getStructure(string $id): ?array
    {
        return $this->structure[$id] ?? null;
    }
}
