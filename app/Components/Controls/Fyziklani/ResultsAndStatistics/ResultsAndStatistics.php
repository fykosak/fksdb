<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics;

use FKSDB\Authorization\EventAuthorizator;
use FKSDB\Components\React\AjaxComponent;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\Modules\FyziklaniModule\BasePresenter;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\ArgumentOutOfRangeException;
use Nette\DI\Container;
use Nette\Utils\DateTime;

/**
 * Class ResultsAndStatistics
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ResultsAndStatistics extends AjaxComponent {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;
    private ServiceFyziklaniTask $serviceFyziklaniTask;
    private ServiceFyziklaniSubmit $serviceFyziklaniSubmit;
    private EventAuthorizator $eventAuthorizator;
    private ModelEvent $event;

    private ?string $lastUpdated = null;

    public function __construct(Container $container, ModelEvent $event, string $reactId) {
        parent::__construct($container, $reactId);
        $this->event = $event;
    }

    final protected function getEvent(): ModelEvent {
        return $this->event;
    }

    final public function injectPrimary(
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        EventAuthorizator $eventAuthorizator
    ): void {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /**
     * @param string $lastUpdated
     * @return void
     * @throws AbortException
     */
    public function handleRefresh(string $lastUpdated): void {
        $this->lastUpdated = $lastUpdated;
        $this->sendAjaxResponse();
    }

    /**
     * @return array
     * @throws NotSetGameParametersException
     */
    protected function getData(): array {
        $gameSetup = $this->getEvent()->getFyziklaniGameSetup();

        $presenter = $this->getPresenter();
        if (!$presenter instanceof BasePresenter) {
            throw new ArgumentOutOfRangeException();
        }
        $isOrg = $this->eventAuthorizator->isContestOrgAllowed('fyziklani.results', 'presentation', $this->getEvent());

        $result = [
            'availablePoints' => $gameSetup->getAvailablePoints(),
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => $gameSetup->game_start->format('c'),
            'gameEnd' => $gameSetup->game_end->format('c'),
            'times' => [
                'toStart' => strtotime($gameSetup->game_start) - time(),
                'toEnd' => strtotime($gameSetup->game_end) - time(),
                'visible' => $this->isResultsVisible(),
            ],
            'lastUpdated' => (new DateTime())->format('c'),
            'isOrg' => $isOrg,
            'refreshDelay' => $gameSetup->refresh_delay,
            'tasksOnBoard' => $gameSetup->tasks_on_board,
            'submits' => [],
        ];

        if ($isOrg || $this->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmitsAsArray($this->getEvent(), $this->lastUpdated);
        }
        // probably need refresh before competition started
        //if (!$this->lastUpdated) {
        $result['teams'] = $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent());
        $result['tasks'] = $this->serviceFyziklaniTask->getTasksAsArray($this->getEvent());
        $result['categories'] = ['A', 'B', 'C'];
        //  }
        return $result;
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    protected function getActions(): array {
        return [
            'refresh' => $this->link('refresh!', ['lastUpdated' => (new DateTime())->format('c')]),
        ];
    }

    /**
     * @return bool
     * @throws NotSetGameParametersException
     */
    private function isResultsVisible(): bool {
        return $this->getEvent()->getFyziklaniGameSetup()->isResultsVisible();
    }
}
