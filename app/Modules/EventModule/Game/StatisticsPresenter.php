<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

class StatisticsPresenter extends BasePresenter
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
        return $this->isAllowed('game.statistics', 'default');
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
        return $this->isAllowed('game.statistics', 'default');
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
        return $this->isAllowed('game.statistics', 'default');
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
        return $this->isAllowed('game.statistics', 'default');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTeamStatistics(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.statistics.team');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTaskStatistics(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.statistics.task');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCorrelationStatistics(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent(
            $this->getContext(),
            $this->getEvent(),
            'fyziklani.statistics.correlation'
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTable(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.results.table');
    }
}
