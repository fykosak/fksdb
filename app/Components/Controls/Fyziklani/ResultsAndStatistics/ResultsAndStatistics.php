<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics;

use FKSDB\Components\React\AjaxComponent;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Modules\FyziklaniModule\BasePresenter;
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

    private ModelEvent $event;

    /** @var string|null */
    private $lastUpdated = null;

    /**
     * FyziklaniReactControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     * @param string $reactId
     */
    public function __construct(Container $container, ModelEvent $event, string $reactId) {
        parent::__construct($container, $reactId);
        $this->event = $event;
    }

    final protected function getEvent(): ModelEvent {
        return $this->event;
    }

    public function injectPrimary(
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniTeam $serviceFyziklaniTeam
    ): void {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
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
