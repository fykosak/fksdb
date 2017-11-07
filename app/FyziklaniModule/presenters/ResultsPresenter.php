<?php

namespace FyziklaniModule;

use BrawlLib\Components\Results;
use Nette\Application\Responses\JsonResponse;
use Nette\DateTime;
use ORM\Models\Events\ModelFyziklaniTeam;

class ResultsPresenter extends BasePresenter {

    protected function unauthorizedAccess() {
        if ($this->getAction() == 'default') {
            return;
        }

        parent::unauthorizedAccess();
    }

    public function requiresLogin() {
        return $this->getAction() != 'default';
    }

    public function renderDefault() {

        if ($this->isAjax()) {
            $isOrg = $this->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getCurrentEvent());
            if ($this->getHttpRequest()->getQuery('type') === 'LANG') {
                $response = [];
                foreach (json_decode($this->getHttpRequest()->getQuery('keys')) as $key) {
                    $response[$key] = _($key);
                }
                $this->sendResponse(new JsonResponse($response));
            } else {
                /**
                 * @var DateTime $lastUpdated
                 */
                $lastUpdated = $this->getHttpRequest()->getQuery('lastUpdated');

                $result = [];
                $result['lastUpdated'] = (new DateTime())->__toString();
                if (!$lastUpdated) {
                    $result['tasks'] = $this->getTasks();
                    $result['teams'] = $this->getTeams();
                }
                $result['submits'] = [];
                $result['isOrg'] = $isOrg;
                if ($isOrg || $this->isResultsVisible()) {
                    $result['submits'] = $this->getSubmits($lastUpdated);
                }
                $result['refreshDelay'] = $this->getCurrentEvent()->getParameter('refreshDelay');
                $result['times'] = [
                    'gameStart' => $this->getCurrentEvent()->getParameter('gameStart')->format(\DateTime::ISO8601),
                    'gameEnd' => $this->getCurrentEvent()->getParameter('gameEnd')->format(\DateTime::ISO8601),
                    'toStart' => strtotime($this->getCurrentEvent()->getParameter('gameStart')) - time(),
                    'toEnd' => strtotime($this->getCurrentEvent()->getParameter('gameEnd')) - time(),
                    'visible' => $this->isResultsVisible()
                ];
                $this->sendResponse(new JsonResponse($result));
            }
        }
    }

    public function createComponentResults() {
        $control = new Results();
        $control->setBasePath($this->getHttpRequest()->getUrl()->getBasePath());
        return $control;
    }

    private function getTasks() {
        $tasks = [];
        /**
         * @var $task \ModelFyziklaniTask
         */
        foreach ($this->serviceFyziklaniTask->findAll($this->eventID)->order('label') as $task) {
            $tasks[] = [
                'label' => $task->label,
                'task_id' => $task->fyziklani_task_id
            ];
        }
        return $tasks;
    }

    private function getTeams() {
        $teams = [];
        /**
         * @var $team ModelFyziklaniTeam
         */
        foreach ($this->serviceFyziklaniTeam->findParticipating($this->eventID) as $team) {
            $teams[] = [
                'category' => $team->category,
                'room' => $team->room,
                'name' => $team->name,
                'team_id' => $team->e_fyziklani_team_id
            ];
        }
        return $teams;
    }

    private function getSubmits($lastUpdated = null) {
        $query = $this->serviceFyziklaniSubmit->getTable()->where('e_fyziklani_team.event_id', $this->eventID);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        /**
         * @var $submit \ModelFyziklaniSubmit
         */
        foreach ($query as $submit) {
            $submits[$submit->fyziklani_submit_id] = [
                'points' => $submit->points,
                'team_id' => $submit->e_fyziklani_team_id,
                'task_id' => $submit->fyziklani_task_id,
                'created' => $submit->created->format(\DateTime::ISO8601)
            ];
        }
        return $submits;
    }

    public function titleDefault() {
        $this->setTitle(_('Výsledky FYKOSího Fyziklání'));
    }

    public function authorizedDefault() {
        $this->setAuthorized(true);
    }

    private function isResultsVisible() {
        $hardDisplay = $this->getCurrentEvent()->getParameter('resultsHardDisplay');
        $before = (time() < strtotime($this->getCurrentEvent()->getParameter('resultsHide')));
        $after = (time() > strtotime($this->getCurrentEvent()->getParameter('resultsDisplay')));

        return $hardDisplay || ($before && $after);
    }
}
