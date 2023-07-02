<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\UI\PageTitle;
use Nette\ComponentModel\IComponent;

trait ChartPresenterTrait
{
    /** @var Chart|IComponent */
    protected Chart $selectedChart;
    private array $chartComponents;

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Charts'), 'fas fa-chart-pie');
    }

    abstract public function authorizedList(): bool;

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
    abstract protected function getCharts(): array;

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function registerCharts(): void
    {
        foreach ($this->getCharts() as $name => $component) {
            $this->addComponent($component, $name);
        }
    }
}
