<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\UI\PageTitle;
use Nette\ComponentModel\IComponent;

trait ChartPresenterTrait
{
    /** @phpstan-var (Chart&IComponent)[] */
    private array $chartComponents;

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Charts'), 'fas fa-chart-pie');
    }

    abstract public function authorizedList(): bool;

    /**
     * @throws EventNotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws NotImplementedException
     */
    final public function renderList(): void
    {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @phpstan-return (Chart&IComponent)[]
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    abstract protected function getCharts(): array;

    /**
     * @throws EventNotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws NotImplementedException
     */
    protected function registerCharts(): void
    {
        foreach ($this->getCharts() as $name => $component) {
            $this->addComponent($component, $name);
        }
    }
}
