<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use Nette\SmartObject;

/**
 * @phpstan-type Item array{
 *      'presenter':string,
 *      'action':string,
 *      'params':array<string,int|string|bool|null>,
 *      'fragment':string
 * }
 * @phpstan-type RootItem array{
 *      'presenter':string,
 *      'action':string,
 *      'params':array<string,int|string|bool|null>,
 *      'fragment':string,'parents':Item[]
 * }
 */
class NavigationFactory
{
    use SmartObject;

    /**
     * @phpstan-var array<string,RootItem>
     */
    private array $structure;

    public function setStructure(array $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @phpstan-return RootItem|null
     */
    public function getStructure(string $id): ?array
    {
        return $this->structure[$id] ?? null;
    }
}
