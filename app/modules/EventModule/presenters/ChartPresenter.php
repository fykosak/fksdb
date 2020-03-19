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
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'list'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedChart() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'chart'));
    }

    public function titleList() {
        $this->setTitle(_('Charts'));
        $this->setIcon('fa fa fa-pie-chart');
    }

    public function startup() {
        parent::startup();
        $this->selectChart();
    }

    public function renderList() {
        $this->template->charts = $this->getCharts();
    }

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

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return 'event.chart';
    }
}
