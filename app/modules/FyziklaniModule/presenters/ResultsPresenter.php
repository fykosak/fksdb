<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;
use Nette\Application\BadRequestException;

/**
 * Class ResultsPresenter
 * @package FyziklaniModule
 */
class ResultsPresenter extends BasePresenter {

    public function titleCorrelationStatistics() {
        $this->setTitle(_('Correlation statistics'), 'fa fa-pie-chart');
    }

    public function titleList() {
        $this->setTitle(_('Results and statistics'), 'fa fa-trophy');
    }

    public function titleTable() {
        $this->setTitle(_('Detailed results'), 'fa fa-trophy');
    }

    public function titlePresentation() {
        $this->setTitle(_('Results presentation'), 'fa fa-table');
    }

    public function titleTeamStatistics() {
        $this->setTitle(_('Teams statistics'), 'fa fa-line-chart');
    }

    public function titleTaskStatistics() {
        $this->setTitle(_('Tasks statistics'), 'fa fa-pie-chart');
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'list'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedResultsTable() {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'table'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedTaskStatistics() {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'taskStatistics'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedTeamStatistics() {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'teamStatistics'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedCorrelationStatistics() {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.results', 'correlation'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedPresentation() {
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
