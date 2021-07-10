<?php

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\UI\PageTitle;

class ResultsPresenter extends BasePresenter {

    public function titleCorrelationStatistics(): void {
        $this->setPageTitle(new PageTitle(_('Correlation statistics'), 'fas fa-chart-pie'));
    }

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Results and statistics'), 'fas fa-chart-area'));
    }

    public function titleTable(): void {
        $this->setPageTitle(new PageTitle(_('Detailed results'), 'fas fa-info'));
    }

    public function titlePresentation(): void {
        $this->setPageTitle(new PageTitle(_('Results presentation'), 'fas fa-chalkboard'));
    }

    public function titleTeamStatistics(): void {
        $this->setPageTitle(new PageTitle(_('Teams statistics'), 'fas fa-chart-line'));
    }

    public function titleTaskStatistics(): void {
        $this->setPageTitle(new PageTitle(_('Tasks statistics'), 'fas fa-chart-bar'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'list'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedResultsTable(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'table'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedTaskStatistics(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'taskStatistics'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedTeamStatistics(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'teamStatistics'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedCorrelationStatistics(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'correlation'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedPresentation(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'presentation'));
    }

    /**
     * @return ResultsAndStatisticsComponent
     * @throws EventNotFoundException
     */
    protected function createComponentTable(): ResultsAndStatisticsComponent {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.results.table');
    }

    /**
     * @return ResultsAndStatisticsComponent
     * @throws EventNotFoundException
     */
    protected function createComponentPresentation(): ResultsAndStatisticsComponent {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.results.presentation');
    }

    /**
     * @return ResultsAndStatisticsComponent
     * @throws EventNotFoundException
     */
    protected function createComponentTeamStatistics(): ResultsAndStatisticsComponent {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.statistics.team');
    }

    /**
     * @return ResultsAndStatisticsComponent
     * @throws EventNotFoundException
     */
    protected function createComponentTaskStatistics(): ResultsAndStatisticsComponent {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.statistics.task');
    }

    /**
     * @return ResultsAndStatisticsComponent
     * @throws EventNotFoundException
     */
    protected function createComponentCorrelationStatistics(): ResultsAndStatisticsComponent {
        return new ResultsAndStatisticsComponent($this->getContext(), $this->getEvent(), 'fyziklani.statistics.correlation');
    }

    protected function beforeRender(): void {
        switch ($this->getAction()) {
            case 'table':
                $this->getPageStyleContainer()->setWidePage();
        }
        parent::beforeRender();
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        $roots = parent::getNavRoots();
        $roots[] = 'Fyziklani.Results.default';
        return $roots;
    }
}
