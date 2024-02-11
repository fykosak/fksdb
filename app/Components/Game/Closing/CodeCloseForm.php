<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\NoTaskLeftException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

final class CodeCloseForm extends FormComponent
{
    private EventModel $event;
    private Handler $handler;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->handler = new Handler($this->container);
    }

    protected function handleSuccess(Form $form): void
    {
        $codeProcessor = new TaskCodePreprocessor($this->event);
        try {
            /** @phpstan-var array{code:string} $values */
            $values = $form->getValues('array');
            $code = $values['code'];
            $team = $codeProcessor->getTeam($code);
            $expectedTask = Handler::getNextTask($team);
            try {
                $givenTask = $codeProcessor->getTask($code);
            } catch (NoTaskLeftException $exception) {
                $givenTask = null;
            }
            if ($expectedTask && !$givenTask) {
                throw new GameException(
                    _('Final task mismatch') . ': ' .
                    sprintf(_('system expected task %s on top.'), $expectedTask->label)
                );
            } elseif (!$expectedTask && $givenTask) {
                throw new GameException(
                    _('Final task mismatch') . ': ' .
                    _('system expected no task left.')
                );
            } elseif ($givenTask->getPrimary() !== $expectedTask->getPrimary()) {
                throw new GameException(
                    _('Final task mismatch') . ': ' .
                    sprintf(_('system expected task %s on top.'), $expectedTask->label)
                );
            }
            $logger = new MemoryLogger();
            $this->handler->close($logger, $team);
            FlashMessageDump::dump($logger, $this->getPresenter());
            $this->getPresenter()->redirect('list', ['id' => null]);
        } catch (GameException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->flashMessage('Undefined error', Message::LVL_ERROR);
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('button.close.submitting'));
    }

    protected function configureForm(Form $form): void
    {
        $codeInput = $form->addText('code', _('Task code'));
        $codeInput->setOption(
            'description',
            _('Kód z úlohy ktorá ostala ako daľšia na vydavanie, prip. posledný papierik') // TODO preklad
        );
    }
}
