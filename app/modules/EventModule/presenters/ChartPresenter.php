<?php

namespace EventModule;

use FKSDB\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\ParticipantAcquaintanceChartControl;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class ChartPresenter
 * @package EventModule
 */
class ChartPresenter extends BasePresenter {

    use ChartPresenterTrait;

    /**
     * @return array
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function registerCharts(): array {
        return [
            new ParticipantAcquaintanceChartControl($this->context, $this->getEvent(), $this->serviceEvent),
        ];
    }

    public function startup() {
        parent::startup();
        $this->selectChart();
    }

    public function renderList() {
        $this->template->charts = $this->getCharts();
    }
}
