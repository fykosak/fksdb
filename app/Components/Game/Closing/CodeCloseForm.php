<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class CodeCloseForm extends FormComponent
{
    private EventModel $event;
    private Handler $handler;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->handler = new Handler($this->container);
    }

    protected function handleSuccess(SubmitButton $button): void
    {
        $code = $button->getForm()->getForm()->getValues('array')['code'];
        /** @var TeamModel2|null $team */
        $team = TaskCodePreprocessor::getTeam($code, $this->event);
        $team->canClose();

        $finalTask = TaskCodePreprocessor::getTask($code, $this->event);
        $task = $this->handler->getNextTask($team);
        if ($task) {
            if ($task->getPrimary() !== $finalTask->getPrimary()) {
                throw new GameException(_('Final task miss match'));
            }
        }
        $this->handler->close($team);
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        // TODO: Implement appendSubmitButton() method.
    }

    protected function configureForm(Form $form): void
    {
        // TODO: Implement configureForm() method.
    }
}
