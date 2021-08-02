<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\UI\PageTitle;
use Nette\ComponentModel\IComponent;

trait ChartPresenterTrait
{

    protected Chart $selectedChart;
    private array $chartComponents;

    public function titleChart(): void
    {
        $this->setPageTitle(new PageTitle($this->selectedChart->getTitle(), 'fas fa-chart-pie'));
    }

    public function titleList(): void
    {
        $this->setPageTitle(new PageTitle(_('Charts'), 'fas fa-chart-pie'));
    }

    final public function renderChart(): void
    {
        $this->template->chart = $this->selectedChart;
    }

    final public function renderList(): void
    {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @return Chart[]
     */
    protected function getCharts(): array
    {
        $this->chartComponents = $this->chartComponents ?? $this->registerCharts();
        return $this->chartComponents;
    }

    protected function selectChart(): void
    {
        $charts = $this->getCharts();
        $action = $this->getAction();
        if (isset($charts[$action])) {
            $this->selectedChart = $charts[$action];
            $this->setView('chart');
        }
    }

    protected function createComponentChart(): IComponent
    {
        return $this->selectedChart->getControl();
    }

    abstract public function authorizedList(): void;

    abstract public function authorizedChart(): void;

    /**
     * @return Chart[]
     */
    abstract protected function registerCharts(): array;

    abstract public function getAction(bool $fullyQualified = false): string;

    /**
     * @param string $id
     * @return static
     */
    abstract public function setView(string $id);
}
