<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results\ResultsPresentation;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results\ResultsView;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\CorrelationStatistics;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\TaskStatistics;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\TeamStatistics;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class ResultsPresenter
 * @package FyziklaniModule
 */
class ResultsPresenter extends BasePresenter {
    /**
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    public function requiresLogin(): bool {
        switch ($this->getAction()) {
            case 'default':
            case 'resultsView':
            case 'taskStatistics':
            case 'teamStatistics':
                return false;
            case 'resultsFinal':
                return !$this->getGameSetup()->result_hard_display;
            default:
                return parent::requiresLogin();
        }
    }

    public function titleCorrelationStatistics() {
        $this->setTitle(_('Correlation statistics'));
        $this->setIcon('fa fa-pie-chart');
    }

    public function titleDefault() {
        $this->setTitle(_('Results and statistics'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsView() {
        $this->setTitle(_('Detailed results'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsPresentation() {
        $this->setIcon('fa fa-table');
        return $this->setTitle(_('Results presentation'));
    }

    public function titleTeamStatistics() {
        $this->setTitle(_('Teams statistics'));
        $this->setIcon('fa fa-line-chart');
    }

    public function titleTaskStatistics() {
        $this->setTitle(_('Tasks statistics'));
        $this->setIcon('fa fa-pie-chart');
    }

    public function authorizedDefault() {
        $this->setAuthorized(true);
    }

    public function authorizedResultsView() {
        $this->authorizedDefault();
    }

    public function authorizedTaskStatistics() {
        $this->authorizedDefault();
    }

    public function authorizedTeamStatistics() {
        $this->authorizedDefault();
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedCorrelationStatistics() {
        $this->setAuthorized($this->isContestsOrgAllowed('fyziklani.results', 'correlation'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedResultsPresentation() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.results', 'presentation'));
    }

    /**
     * @return ResultsAndStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentResultsView(): ResultsAndStatistics {
        return $this->fyziklaniComponentsFactory->createResultsAndStatistics('fyziklani.results.view', $this->getEvent());
    }

    /**
     * @return ResultsAndStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentResultsPresentation(): ResultsAndStatistics {
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
