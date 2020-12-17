<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Model\UI\PageTitle;
use Nette\ComponentModel\IComponent;

/**
 * Trait ChartPresenterTrait
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait ChartPresenterTrait {

    protected IChart $selectedChart;

    private array $chartComponents;

    public function titleChart(): void {
        $this->setPageTitle(new PageTitle($this->selectedChart->getTitle(), 'fa fa-pie-chart'));
    }

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Charts'), 'fa fa fa-pie-chart'));
    }

    public function renderChart(): void {
        $this->template->chart = $this->selectedChart;
    }

    public function renderList(): void {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @return IChart[]
     */
    protected function getCharts(): array {
        $this->chartComponents = $this->chartComponents ?? $this->registerCharts();
        return $this->chartComponents;
    }

    protected function selectChart(): void {
        $charts = $this->getCharts();
        $action = $this->getAction();
        if (isset($charts[$action])) {
            $this->selectedChart = $charts[$action];
            $this->setView('chart');
        }
    }

    protected function createComponentChart(): IComponent {
        return $this->selectedChart->getControl();
    }

    abstract public function authorizedList(): void;

    abstract public function authorizedChart(): void;

    /**
     * @return IChart[]
     */
    abstract protected function registerCharts(): array;

    abstract public function getAction(bool $fullyQualified = false): string;

    /**
     * @param string $id
     * @return static
     */
    abstract public function setView($id);
}
