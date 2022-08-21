<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Modules\EventModule\Fyziklani\BasePresenter;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Utils\DateTime;

class ResultsAndStatisticsComponent extends AjaxComponent
{
    private SubmitService $submitService;
    private EventModel $event;
    private ?string $lastUpdated = null;

    public function __construct(Container $container, EventModel $event, string $reactId)
    {
        parent::__construct($container, $reactId);
        $this->event = $event;
    }

    final protected function getEvent(): EventModel
    {
        return $this->event;
    }

    final public function injectPrimary(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    public function handleRefresh(string $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
        $this->sendAjaxResponse();
    }

    /**
     * @throws NotSetGameParametersException
     * @throws BadTypeException
     */
    protected function getData(): array
    {
        $gameSetup = $this->getEvent()->getFyziklaniGameSetup();

        $presenter = $this->getPresenter();
        if (!$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }

        $result = [
            'availablePoints' => $gameSetup->getAvailablePoints(),
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => $gameSetup->game_start->format('c'),
            'gameEnd' => $gameSetup->game_end->format('c'),
            'times' => [
                'toStart' => $gameSetup->game_start->getTimestamp() - time(),
                'toEnd' => $gameSetup->game_end->getTimestamp() - time(),
                'visible' => $this->isResultsVisible(),
            ],
            'lastUpdated' => (new DateTime())->format('c'),
            'isOrg' => true,
            'refreshDelay' => $gameSetup->refresh_delay,
            'tasksOnBoard' => $gameSetup->tasks_on_board,
            'submits' => [],
        ];

        $result['submits'] = $this->submitService->serialiseSubmits($this->getEvent(), $this->lastUpdated);

        // probably need refresh before competition started
        //if (!$this->lastUpdated) {
        $result['teams'] = TeamService2::serialiseTeams($this->getEvent());
        $result['tasks'] = TaskService::serialiseTasks($this->getEvent());
        $result['categories'] = array_map(
            fn(TeamCategory $category): string => $category->value,
            TeamCategory::casesForEvent($this->getEvent())
        );
        //  }
        return $result;
    }

    /**
     * @throws InvalidLinkException
     */
    protected function configure(): void
    {
        $this->addAction('refresh', 'refresh!', ['lastUpdated' => (new DateTime())->format('c')]);
        parent::configure();
    }

    /**
     * @throws NotSetGameParametersException
     */
    private function isResultsVisible(): bool
    {
        return $this->getEvent()->getFyziklaniGameSetup()->isResultsVisible();
    }
}
