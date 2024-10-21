<?php

declare(strict_types=1);

namespace FKSDB\Components\Choosers;

use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class SeriesChooserComponent extends ChooserComponent
{
    private int $series;
    /** @phpstan-var int[] */
    private array $allowedSeries;

    /**
     * @phpstan-param int[] $allowedSeries
     */
    public function __construct(Container $container, int $series, array $allowedSeries)
    {
        parent::__construct($container);
        $this->series = $series;
        $this->allowedSeries = $allowedSeries;
    }

    protected function getItem(): NavItem
    {
        $items = [];
        foreach ($this->allowedSeries as $series) {
            $items[] = new NavItem(
                new Title(null, sprintf(_('Series %d'), $series)),
                'this',
                ['series' => $series],
                [],
                $series === $this->series
            );
        }
        return new NavItem(new Title(null, sprintf(_('Series %d'), $this->series)), '#', [], $items);
    }
}
