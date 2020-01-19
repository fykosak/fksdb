<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\FinalResults;
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
     * @return ResultsView
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentResultsView(): ResultsView {
        return $this->fyziklaniComponentsFactory->createResultsView($this->getEvent());
    }

    /**
     * @return ResultsPresentation
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentResultsPresentation(): ResultsPresentation {
        return $this->fyziklaniComponentsFactory->createResultsPresentation($this->getEvent());
    }

    /**
     * @return TeamStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentTeamStatistics(): TeamStatistics {
        return $this->fyziklaniComponentsFactory->createTeamStatistics($this->getEvent());
    }

    /**
     * @return TaskStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentTaskStatistics(): TaskStatistics {
        return $this->fyziklaniComponentsFactory->createTaskStatistics($this->getEvent());
    }

    /**
     * @return CorrelationStatistics
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentCorrelationStatistics(): CorrelationStatistics {
        return $this->fyziklaniComponentsFactory->createCorrelationStatistics($this->getEvent());
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
