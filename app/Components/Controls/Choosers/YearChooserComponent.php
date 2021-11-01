<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\ModelContestYear;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\GroupedSelection;
use Nette\DI\Container;

final class YearChooserComponent extends ChooserComponent
{

    public const ROLE_ORG = 'org';
    public const ROLE_CONTESTANT = 'contestant';
    public const ROLE_ALL = 'all';
    public const ROLE_SELECTED = 'selected';

    private ?ModelContestYear $contestYear;
    private GroupedSelection $availableYears;

    public function __construct(Container $container, ?ModelContestYear $urlYear, GroupedSelection $availableYears)
    {
        parent::__construct($container);
        $this->contestYear = $urlYear;
        $this->availableYears = $availableYears;
    }

    protected function getItem(): NavItem
    {
        $items = [];
        foreach ($this->availableYears as $row) {
            $year = ModelContestYear::createFromActiveRow($row);
            $items[] = new NavItem(
                new Title(sprintf(_('Year %d'), $year->year)),
                'this',
                ['year' => $year->year],
                [],
                $this->contestYear->year === $year->year
            );
        }
        return new NavItem(new Title(sprintf(_('Year %d'), $this->contestYear->year)), '#', [], $items);
    }
}
