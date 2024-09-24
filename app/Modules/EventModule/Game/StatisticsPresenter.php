<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

final class StatisticsPresenter extends BasePresenter
{
    /**
     * @throws EventNotFoundException
     * @throws NotSetGameParametersException
     */
    public function requiresLogin(): bool
    {
        return !$this->getEvent()->getGameSetup()->result_hard_display;
    }

    public function titleCorrelation(): PageTitle
    {
        return new PageTitle(null, _('Correlation statistics'), 'fas fa-chart-pie');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedCorrelation(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource('game', $this->getEvent()),
            'statistics.correlation',
            $this->getEvent()
        );
    }

    public function titleTeam(): PageTitle
    {
        return new PageTitle(null, _('Teams statistics'), 'fas fa-chart-line');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedTeam(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource('game', $this->getEvent()),
            'statistics.team',
            $this->getEvent()
        );
    }

    public function titleTask(): PageTitle
    {
        return new PageTitle(null, _('Tasks statistics'), 'fas fa-chart-bar');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedTask(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource('game', $this->getEvent()),
            'statistics.task',
            $this->getEvent()
        );
    }

    public function titleTable(): PageTitle
    {
        return new PageTitle(null, _('Detailed results'), 'fas fa-info');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedTable(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource('game', $this->getEvent()),
            'statistics.table',
            $this->getEvent()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTeamStatistics(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'game.statistics.team');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTaskStatistics(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'game.statistics.task');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCorrelationStatistics(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent(
            $this->getContext(),
            $this->getEvent(),
            'game.statistics.correlation'
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTable(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'game.results.table');
    }
}
