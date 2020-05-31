<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;
use Nette\Application\BadRequestException;

/**
 * Class ResultsPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ResultsPresenter extends BasePresenter {
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCorrelationStatistics(): void {
        $this->setTitle(_('Correlation statistics'), 'fa fa-pie-chart');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList(): void {
        $this->setTitle(_('Results and statistics'), 'fa fa-trophy');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleTable(): void {
        $this->setTitle(_('Detailed results'), 'fa fa-trophy');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titlePresentation(): void {
        $this->setTitle(_('Results presentation'), 'fa fa-table');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleTeamStatistics(): void {
        $this->setTitle(_('Teams statistics'), 'fa fa-line-chart');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleTaskStatistics(): void {
        $this->setTitle(_('Tasks statistics'), 'fa fa-pie-chart');
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'list'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedResultsTable(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'table'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedTaskStatistics(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'taskStatistics'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedTeamStatistics(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'teamStatistics'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedCorrelationStatistics(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'correlation'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedPresentation(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'presentation'));
    }

    /**
     * @return ResultsAndStatistics
     * @throws BadRequestException
     */
    public function createComponentTable(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.results.table');
    }

    /**
     * @return ResultsAndStatistics
     * @throws BadRequestException
     */
    public function createComponentPresentation(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.results.presentation');
    }

    /**
     * @return ResultsAndStatistics
     * @throws BadRequestException
     */
    public function createComponentTeamStatistics(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.statistics.team');
    }

    /**
     * @return ResultsAndStatistics
     * @throws BadRequestException
     */
    public function createComponentTaskStatistics(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.statistics.task');
    }

    /**
     * @return ResultsAndStatistics
     * @throws BadRequestException
     */
    public function createComponentCorrelationStatistics(): ResultsAndStatistics {
        return new ResultsAndStatistics($this->getContext(), $this->getEvent(), 'fyziklani.statistics.correlation');
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
