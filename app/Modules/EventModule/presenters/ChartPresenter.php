<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Events\EventNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\Event\ParticipantAcquaintanceChartControl;
use FKSDB\Components\React\ReactComponent\Events\SingleApplicationsTimeProgress;
use FKSDB\Components\React\ReactComponent\Events\TeamApplicationsTimeProgress;

/**
 * Class ChartPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList() {
        $this->setAuthorized($this->isContestsOrgAuthorized($this->getModelResource(), 'list'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedChart() {
        $this->setAuthorized($this->isContestsOrgAuthorized($this->getModelResource(), 'chart'));
    }

    protected function startup() {
        parent::startup();
        $this->selectChart();
    }

    /**
     * @return array
     * @throws EventNotFoundException
     */
    protected function registerCharts(): array {
        return [
            'participantAcquaintance' => new ParticipantAcquaintanceChartControl($this->getContext(), $this->getEvent()),
            'singleApplicationProgress' => new SingleApplicationsTimeProgress($this->getContext(), $this->getEvent()),
            'teamApplicationProgress' => new TeamApplicationsTimeProgress($this->getContext(), $this->getEvent()),
        ];
    }

    protected function getModelResource(): string {
        return 'event.chart';
    }
}
