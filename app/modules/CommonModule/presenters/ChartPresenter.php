<?php

namespace CommonModule;

use FKSDB\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\TotalPersonsChartControl;
use Nette\Application\BadRequestException;

/**
 * Class ChartPresenter
 * @package CommonModule
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    public function authorizedList() {
        $this->setAuthorized($this->isAnyContestAuthorized($this->getModelResource(), 'list'));
    }

    public function authorizedChart() {
        $this->setAuthorized($this->isAnyContestAuthorized($this->getModelResource(), 'chart'));
    }

    public function startup() {
        parent::startup();
        $this->selectChart();
    }

    /**
     * @inheritDoc
     */
    protected function registerCharts(): array {
        return [
            new TotalPersonsChartControl($this->context),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return 'chart';
    }
}
