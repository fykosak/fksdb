<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;
use FKSDB\Events\Exceptions\EventNotFoundException;
use FKSDB\UI\PageTitle;

/**
 * Class ResultsPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ResultsPresenter extends BasePresenter {
    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleCorrelationStatistics(): void {
        $this->setPageTitle(new PageTitle(_('Correlation statistics'), 'fa fa-pie-chart'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Results and statistics'), 'fa fa-trophy'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleTable(): void {
        $this->setPageTitle(new PageTitle(_('Detailed results'), 'fa fa-trophy'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titlePresentation(): void {
        $this->setPageTitle(new PageTitle(_('Results presentation'), 'fa fa-table'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleTeamStatistics(): void {
        $this->setPageTitle(new PageTitle(_('Teams statistics'), 'fa fa-line-chart'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleTaskStatistics(): void {
        $this->setPageTitle(new PageTitle(_('Tasks statistics'), 'fa fa-pie-chart'));
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
     * @return ResultsAndStatistics
     * @throws EventNotFoundException
     */
    protected function createComponentTable(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.results.table');
    }

    /**
     * @return ResultsAndStatistics
     * @throws EventNotFoundException
     */
    protected function createComponentPresentation(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.results.presentation');
    }

    /**
     * @return ResultsAndStatistics
     * @throws EventNotFoundException
     */
    protected function createComponentTeamStatistics(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.statistics.team');
    }

    /**
     * @return ResultsAndStatistics
     * @throws EventNotFoundException
     */
    protected function createComponentTaskStatistics(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.statistics.task');
    }

    /**
     * @return ResultsAndStatistics
     * @throws EventNotFoundException
     */
    protected function createComponentCorrelationStatistics(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.statistics.correlation');
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
