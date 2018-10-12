<?php

namespace FyziklaniModule;

use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsPresentation;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsView;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TaskStatistics;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TeamStatistics;

class ResultsPresenter extends BasePresenter {
    //use \ReactRequest;

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

    public function requiresLogin() {
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
        $this->setTitle(_('Výsledky a statistiky FYKOSího Fyziklání'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsView() {
        $this->setTitle(_('Výsledky FYKOSího Fyziklání'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsPresentation() {
        $this->setIcon('fa fa-table');
        return $this->setTitle(_('Presentace FYKOSího Fyziklání'));
    }

    public function titleTeamStatistics() {
        $this->setTitle(_('Tímové statistiky FYKOSího Fyzikláni'));
        $this->setIcon('fa fa-line-chart');
    }

    public function titleTaskStatistics() {
        $this->setTitle(_('Statistiky úloh FYKOSího Fyzikláni'));
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

    public function authorizedResultsPresentation() {
        $this->getHttpRequest();
        $this->setAuthorized($this->eventIsAllowed('fyziklani', 'presentation'));
    }

    public function createComponentResultsView() {
        return new ResultsView($this->context, $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->getEvent());
    }

    public function createComponentResultsPresentation() {
        return new ResultsPresentation($this->context, $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->getEvent());
    }

    public function createComponentTeamStatistics() {
        return new TeamStatistics($this->context, $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->getEvent());
    }

    public function createComponentTaskStatistics() {
        return new TaskStatistics($this->context, $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->getEvent());
    }
}
