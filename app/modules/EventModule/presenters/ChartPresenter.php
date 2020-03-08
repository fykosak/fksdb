<?php

namespace EventModule;

use FKSDB\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\Event\ParticipantAcquaintanceChartControl;
use FKSDB\Components\React\ReactComponent\Events\SingleApplicationsTimeProgress;
use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;
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
            new ParticipantAcquaintanceChartControl($this->context, $this->getEvent()),
            new SingleApplicationsTimeProgress($this->context, $this->getEvent()),
            new TeamApplicationsTimeProgress($this->context, $this->getEvent()),
        ];
    }

    public function startup() {
        parent::startup();
        $this->selectChart();
    }

    public function renderDefault() {
        $this->template->charts = $this->getCharts();
    }

    public function titleDefault() {
        $this->setTitle(_('Charts'));
        $this->setIcon('fa fa fa-pie-chart');
    }
}
