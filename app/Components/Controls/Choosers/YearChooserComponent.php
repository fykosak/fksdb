<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class YearChooserComponent extends ChooserComponent
{
    private ContestYearModel $contestYear;
    /**
     * @phpstan-var TypedGroupedSelection<ContestYearModel> $availableYears
     */
    private TypedGroupedSelection $availableYears;

    /**
     * @phpstan-param  TypedGroupedSelection<ContestYearModel> $availableYears
     */
    public function __construct(Container $container, ContestYearModel $urlYear, TypedGroupedSelection $availableYears)
    {
        parent::__construct($container);
        $this->contestYear = $urlYear;
        $this->availableYears = $availableYears;
    }

    protected function getItem(): NavItem
    {
        $items = [];
        /** @var ContestYearModel $year */
        foreach ($this->availableYears as $year) {
            $items[] = new NavItem(
                new Title(null, sprintf(_('Year %d'), $year->year)),
                'this',
                ['year' => $year->year],
                [],
                $this->contestYear->year === $year->year
            );
        }
        return new NavItem(new Title(null, sprintf(_('Year %d'), $this->contestYear->year)), '#', [], $items);
    }
}
