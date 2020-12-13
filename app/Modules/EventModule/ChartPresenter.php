<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Chart\Event\Applications\ApplicationRationGeoChart;
use FKSDB\Components\Controls\Chart\Event\Applications\ParticipantsTimeGeoChart;
use FKSDB\Components\Controls\Chart\Event\Applications\TeamsGeoChart;
use FKSDB\Components\Controls\Chart\Event\ApplicationsTimeProgress\SingleComponent;
use FKSDB\Components\Controls\Chart\Event\ApplicationsTimeProgress\TeamComponent;
use FKSDB\Model\Events\Exceptions\EventNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\Event\ParticipantAcquaintance\ParticipantAcquaintanceChart;

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
            'participantAcquaintance' => new ParticipantAcquaintanceChart($this->getContext(), $this->getEvent()),
            'singleApplicationProgress' => new SingleComponent($this->getContext(), $this->getEvent()),
            'teamApplicationProgress' => new TeamComponent($this->getContext(), $this->getEvent()),
            'teamsPerCountry' => new TeamsGeoChart($this->getContext(), $this->getEvent()),
            'ratioPerCountry' => new ApplicationRationGeoChart($this->getContext(), $this->getEvent()),
            'participantsInTimeGeo' => new ParticipantsTimeGeoChart($this->getContext(), $this->getEvent()),
        ];
    }

    protected function getModelResource(): string {
        return 'event.chart';
    }
}
