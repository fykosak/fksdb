<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\UI\PageTitle;
use Nette\Application\UI\Control;

/**
 * Trait ChartPresenterTrait
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait ChartPresenterTrait {
    /**
     * @var IChart
     */
    protected $selectedChart;
    /** @var IChart[] */
    private $chartComponents;

    public function titleChart() {
        $this->setPageTitle(new PageTitle($this->selectedChart->getTitle(), 'fa fa-pie-chart'));
    }

    public function titleList() {
        $this->setPageTitle(new PageTitle(_('Charts'), 'fa fa fa-pie-chart'));
    }

    public function renderChart() {
        $this->template->chart = $this->selectedChart;
    }

    public function renderList() {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @return IChart[]
     */
    protected function getCharts(): array {
        $this->chartComponents = $this->chartComponents ?? $this->registerCharts();
        return $this->chartComponents;
    }

    protected function selectChart() {
        $charts = $this->getCharts();
        $action = $this->getAction();
        if (isset($charts[$action])) {
            $this->selectedChart = $charts[$action];
            $this->setView('chart');
        }
    }

    protected function createComponentChart(): Control {
        return $this->selectedChart->getControl();
    }

    abstract public function authorizedList();

    abstract public function authorizedChart();

    /**
     * @return IChart[]
     */
    abstract protected function registerCharts(): array;

    /**
     * @param bool $fullyQualified
     * @return string
     */
    abstract public function getAction($fullyQualified = false);

    /**
     * @param string $id
     * @return static
     */
    abstract public function setView($id);
}
