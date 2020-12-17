<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Components\Controls\Loaders\IJavaScriptCollector;
use FKSDB\Components\React\AjaxComponent;
use FKSDB\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Fyziklani\Submit\HandlerFactory;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Messages\Message;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\Fyziklani\Submit\TaskCodeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Class TaskCodeInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TaskCodeInput extends AjaxComponent {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ServiceFyziklaniTask $serviceFyziklaniTask;

    private HandlerFactory $handlerFactory;

    private ModelEvent $event;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, 'fyziklani.submit-form');
        $this->event = $event;
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            $collector->registerJSFile('https://dmla.github.io/jsqrcode/src/qr_packed.js');
        });
    }

    final public function injectPrimary(HandlerFactory $handlerFactory, ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @return array
     * @throws NotSetGameParametersException
     */
    protected function getData(): array {
        return [
            'availablePoints' => $this->event->getFyziklaniGameSetup()->getAvailablePoints(),
            'tasks' => $this->serviceFyziklaniTask->getTasksAsArray($this->event),
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->event),
        ];
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    protected function getActions(): array {
        return [
            'save' => $this->link('save!'),
        ];
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleSave(): void {
        $data = (array)json_decode($this->getHttpRequest()->getRawBody());
        try {
            $handler = $this->handlerFactory->create($this->event);
            $handler->preProcess($this->getLogger(), $data['code'], +$data['points']);
        } catch (TaskCodeException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        } catch (ClosedSubmittingException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        }
        $this->sendAjaxResponse();
    }
}
