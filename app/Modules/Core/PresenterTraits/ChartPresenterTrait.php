<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;

trait ChartPresenterTrait
{
    /** @var Chart|IComponent */
    protected Chart $selectedChart;
    private array $chartComponents;

    public function titleChart(): PageTitle
    {
        return new PageTitle(null, $this->selectedChart->getTitle(), 'fas fa-chart-pie');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Charts'), 'fas fa-chart-pie');
    }

    final public function renderChart(): void
    {
        $this->template->chart = $this->selectedChart;
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    final public function renderList(): void
    {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @return Chart[]
     * @throws BadTypeException
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function getCharts(): array
    {
        $this->chartComponents = $this->chartComponents ?? $this->registerCharts();
        return $this->chartComponents;
    }

    /**
     * @return Chart[]
     */
    abstract protected function registerCharts(): array;

    abstract public function authorizedList(): bool;

    abstract public function authorizedChart(): bool;

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     * @throws BadRequestException
     */
    protected function selectChart(): void
    {
        if ($this->getAction() === 'chart') {
            $charts = $this->getCharts();
            $chart = $this->getParameter('chart');
            if (isset($charts[$chart])) {
                $this->selectedChart = $charts[$chart];
            } else {
                throw new BadRequestException(sprintf('Chart %s not found', $chart));
            }
        }
    }

    abstract public function getAction(bool $fullyQualified = false): string;


    protected function createComponentChart(): IComponent
    {
        return $this->selectedChart;
    }
}
