<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Chart\Event\SingleApplicationsTimeProgress;
use FKSDB\Components\Controls\Chart\Event\TeamApplicationsTimeProgress;
use FKSDB\Components\Controls\Chart\GeoCharts\ApplicationRationGeoChartControl;
use FKSDB\Components\Controls\Chart\GeoCharts\ParticipantsInTimeGeoChartControl;
use FKSDB\Components\Controls\Chart\GeoCharts\TeamsGeoChartControl;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\Event\ParticipantAcquaintanceChartControl;

/**
 * Class ChartPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized($this->getModelResource(), 'list'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedChart(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized($this->getModelResource(), 'chart'));
    }

    protected function startup(): void {
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
            'teamsPerCountry' => new TeamsGeoChartControl($this->getContext(), $this->getEvent()),
            'ratioPerCountry' => new ApplicationRationGeoChartControl($this->getContext(), $this->getEvent()),
            'participantsInTimeGeo' => new ParticipantsInTimeGeoChartControl($this->getContext(), $this->getEvent()),
        ];
    }

    protected function getModelResource(): string {
        return 'event.chart';
    }
}
