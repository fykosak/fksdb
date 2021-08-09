<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\DI\Container;

class YearChooserComponent extends ChooserComponent
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

    protected function getTitle(): Title
    {
        return new Title(sprintf(_('Year %d'), $this->contestYear->year));
    }

    protected function getItems(): iterable
    {
        return $this->availableYears;
    }

    /**
     * @param ActiveRow|ModelContestYear $item
     * @return bool
     */
    public function isItemActive($item): bool
    {
        return $item->year === $this->contestYear->year;
    }

    /**
     * @param ActiveRow|ModelContestYear $item
     * @return Title
     */
    public function getItemTitle($item): Title
    {
        return new Title(sprintf(_('Year %d'), $item->year));
    }

    /**
     * @param ActiveRow|ModelContestYear $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string
    {
        return $this->getPresenter()->link('this', ['year' => $item->year]);
    }
}
