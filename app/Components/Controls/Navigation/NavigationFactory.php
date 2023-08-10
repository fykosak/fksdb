<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use Nette\SmartObject;

/**
 * @phpstan-type TItem array{
 *      presenter:string,
 *      action:string,
 *      params:array<string,scalar|null>,
 *      fragment:string
 * }
 * @phpstan-type TRootItem array{
 *      presenter:string,
 *      action:string,
 *      params:array<string,scalar|null>,
 *      fragment:string,
 *      parents:TItem[]
 * }
 */
class NavigationFactory
{
    use SmartObject;

    /**
     * @phpstan-var array<string,TRootItem>
     */
    private array $structure;

    /**
     * @phpstan-param array<string,TRootItem> $structure
     */
    public function setStructure(array $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @phpstan-return TRootItem|null
     */
    public function getStructure(string $id): ?array
    {
        return $this->structure[$id] ?? null;
    }
}
