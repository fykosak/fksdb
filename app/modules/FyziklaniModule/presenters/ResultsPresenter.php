<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class ResultsPresenter
 * @package FyziklaniModule
 */
class ResultsPresenter extends BasePresenter {
    /**
     * @return bool
     */
    public function requiresLogin(): bool {
        switch ($this->getAction()) {
            case 'default':
            case 'table':
            case 'taskStatistics':
            case 'teamStatistics':
                return false;
            default:
                return parent::requiresLogin();
        }
    }

    public function titleCorrelationStatistics(): void {
        $this->setTitle(_('Correlation statistics'));
        $this->setIcon('fa fa-pie-chart');
    }

    public function titleDefault(): void {
        $this->setTitle(_('Results and statistics'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleTable(): void {
        $this->setTitle(_('Detailed results'));
        $this->setIcon('fa fa-trophy');
    }

    public function titlePresentation(): void {
        $this->setIcon('fa fa-table');
        return $this->setTitle(_('Results presentation'));
    }

    public function titleTeamStatistics(): void {
        $this->setTitle(_('Teams statistics'));
        $this->setIcon('fa fa-line-chart');
    }

    public function titleTaskStatistics(): void {
        $this->setTitle(_('Tasks statistics'));
        $this->setIcon('fa fa-pie-chart');
    }

    public function authorizedDefault(): void {
        $this->setAuthorized(true);
    }

    public function authorizedResultsTable(): void {
        $this->authorizedDefault();
    }

    public function authorizedTaskStatistics(): void {
        $this->authorizedDefault();
    }

    public function authorizedTeamStatistics(): void {
        $this->authorizedDefault();
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedCorrelationStatistics(): void {
        $this->setAuthorized($this->isContestsOrgAllowed('fyziklani.results', 'correlation'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedPresentation(): void {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.results', 'presentation'));
    }

    /**
     * @return ResultsAndStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentTable(): ResultsAndStatistics {
        return $this->fyziklaniComponentsFactory->createResultsAndStatistics('fyziklani.results.table', $this->getEvent());
    }

    /**
     * @return ResultsAndStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentPresentation(): ResultsAndStatistics {
        return $this->fyziklaniComponentsFactory->createResultsAndStatistics('fyziklani.results.presentation', $this->getEvent());
    }

    /**
     * @return ResultsAndStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentTeamStatistics(): ResultsAndStatistics {
        return $this->fyziklaniComponentsFactory->createResultsAndStatistics('fyziklani.statistics.team', $this->getEvent());
    }

    /**
     * @return ResultsAndStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentTaskStatistics(): ResultsAndStatistics {
        return $this->fyziklaniComponentsFactory->createResultsAndStatistics('fyziklani.statistics.task', $this->getEvent());
    }

    /**
     * @return ResultsAndStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentCorrelationStatistics(): ResultsAndStatistics {
        return $this->fyziklaniComponentsFactory->createResultsAndStatistics('fyziklani.statistics.correlation', $this->getEvent());
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        $roots = parent::getNavRoots();
        $roots[] = 'fyziklani.results.default';
        return $roots;
    }
}
