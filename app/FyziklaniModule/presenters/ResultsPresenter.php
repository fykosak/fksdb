<?php

namespace FyziklaniModule;

use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsPresentation;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsView;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\CorrelationStatistics;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TaskStatistics;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TeamStatistics;

class ResultsPresenter extends BasePresenter {

    /**
     * @throws \Nette\Application\ForbiddenRequestException
     */
    protected function unauthorizedAccess() {
        switch ($this->getAction()) {
            case 'default':
            case 'resultsView':
            case 'taskStatistics':
            case 'teamStatistics':
                return;
            default:
                parent::unauthorizedAccess();
        }
    }

    public function requiresLogin(): bool {
        switch ($this->getAction()) {
            case 'default':
            case 'resultsView':
            case 'taskStatistics':
            case 'teamStatistics':
                return false;
            default:
                return true;
        }
    }

    public function titleDefault() {
        $this->setTitle(_('Results and statistics of Fyziklani'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsView() {
        $this->setTitle(_('Results of Fyziklani'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsPresentation() {
        $this->setIcon('fa fa-table');
        return $this->setTitle(_('Results presentation of Fyziklani'));
    }

    public function titleTeamStatistics() {
        $this->setTitle(_('Teams statistics of Fyziklani'));
        $this->setIcon('fa fa-line-chart');
    }

    public function titleTaskStatistics() {
        $this->setTitle(_('Tasks statistics of Fyziklani'));
        $this->setIcon('fa fa-pie-chart');
    }

    public function titleCorrelationStatistics() {
        $this->setTitle(_('Correlation statistics'));
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

    public function authorizedCorrelationStatistics() {
        $this->setAuthorized($this->isContestsOrgAllowed('fyziklani', 'correlation'));
    }

    public function authorizedResultsPresentation() {
        $this->getHttpRequest();
        $this->setAuthorized($this->eventIsAllowed('fyziklani', 'presentation'));
    }

    /**
     * @return ResultsView
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentResultsView(): ResultsView {
        return $this->fyziklaniComponentsFactory->createResultsView($this->getEvent());
    }

    /**
     * @return ResultsPresentation
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentResultsPresentation(): ResultsPresentation {
        return $this->fyziklaniComponentsFactory->createResultsPresentation($this->getEvent());
    }

    /**
     * @return TeamStatistics
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentTeamStatistics(): TeamStatistics {
        return $this->fyziklaniComponentsFactory->createTeamStatistics($this->getEvent());
    }

    /**
     * @return TaskStatistics
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentTaskStatistics(): TaskStatistics {
        return $this->fyziklaniComponentsFactory->createTaskStatistics($this->getEvent());
    }

    public function createComponentCorrelationStatistics(): CorrelationStatistics {
        return $this->fyziklaniComponentsFactory->createCorrelationStatistics($this->getEvent());
    }
}
