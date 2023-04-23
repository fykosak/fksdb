<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\Message;
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
        try {
            $code = $button->getForm()->getForm()->getValues('array')['code'];
            $team = TaskCodePreprocessor::getTeam($code, $this->event);
            $givenTask = TaskCodePreprocessor::getTask($code, $this->event);
            $expectedTask = $this->handler->getNextTask($team);
            if ($expectedTask) {
                if ($givenTask->getPrimary() !== $expectedTask->getPrimary()) {
                    throw new GameException(_('Final task mismatch'));
                }
            }
            $this->handler->close($team);
            FlashMessageDump::dump($this->handler->logger, $this->getPresenter());
            $this->getPresenter()->redirect('list', ['id' => null]);
        } catch (GameException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable$exception) {
            $this->flashMessage('Undefined error', Message::LVL_ERROR);
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Close submitting!'));
    }

    protected function configureForm(Form $form): void
    {
        $codeInput = $form->addText('code', _('Task code'));
        $codeInput->setOption(
            'description',
            _('Kód z úlohy ktorá ostala ako daľšia na vydavanie, prip. posledný papierik')
        );
    }
}
