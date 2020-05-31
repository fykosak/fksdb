<?php

namespace EventModule;

use FKSDB\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\Event\ParticipantAcquaintanceChartControl;
use FKSDB\Components\React\ReactComponent\Events\SingleApplicationsTimeProgress;
use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;
use Nette\Application\BadRequestException;

/**
 * Class ChartPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    /**
     * @throws BadRequestException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized($this->getModelResource(), 'list'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedChart(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized($this->getModelResource(), 'chart'));
    }

    public function startup() {
        parent::startup();
        $this->selectChart();
    }

    /**
     * @return array
     * @throws BadRequestException
     */
    protected function registerCharts(): array {
        return [
            new ParticipantAcquaintanceChartControl($this->getContext(), $this->getEvent()),
            new SingleApplicationsTimeProgress($this->getContext(), $this->getEvent()),
            new TeamApplicationsTimeProgress($this->getContext(), $this->getEvent()),
        ];
    }

    protected function getModelResource(): string {
        return 'event.chart';
    }
}
