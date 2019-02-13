<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\TaskCodeException;
use FKSDB\model\Fyziklani\TaskCodeHandler;
use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Forms\Controls\Button;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class QREntryControl
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class QREntryControl extends Control {
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var TaskCodeHandler
     */
    private $handler;
    /**
     * @var string
     */
    private $code;

    /**
     * QREntryControl constructor.
     * @param ModelEvent $event
     * @param TaskCodeHandler $taskCodeHandler
     * @param ITranslator $translator
     */
    public function __construct(ModelEvent $event, TaskCodeHandler $taskCodeHandler, ITranslator $translator) {
        parent::__construct();
        $this->event = $event;
        $this->translator = $translator;
        $this->handler = $taskCodeHandler;
    }

    /**
     * @return FormControl
     */
    public function createComponentForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('task_code')->setAttribute('readonly', true);

        foreach ($this->event->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
            $label = ($points == 1) ? _('bod') : (($points < 5) ? _('body') : _('bodů'));
            $form->addSubmit('points' . $points, _($points . ' ' . $label))
                ->setAttribute('class', 'btn-' . $points . '-points')->setDisabled(true);
        }
        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        $form->onValidate[] = function (Form $form) {
            $this->formValidate($form);
        };
        $form->onSuccess[] = function (Form $form) {
            $this->entryFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code) {
        $this->code = $code;
        try {
            $this->handler->checkTaskCode($code);
        } catch (TaskCodeException $e) {
            $this->getPresenter()->flashMessage($e->getMessage(), \BasePresenter::FLASH_ERROR);
            return;
        } catch (ClosedSubmittingException $e) {
            $this->getPresenter()->flashMessage($e->getMessage(), \BasePresenter::FLASH_ERROR);
        }
        /**
         * @var FormControl $control
         */
        $control = $this->getComponent('form');
        $form = $control->getForm();
        $form->setDefaults(['task_code' => $code]);

        foreach ($this->event->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
            /**
             * @var Button $button
             */
            $button = $form['points' . $points];
            $button->setDisabled(false);
        }
    }

    /**
     * @return Form
     * @throws BadRequestException
     */
    public function getForm(): Form {
        $control = $this->getComponent('form');
        if ($control instanceof FormControl) {
            return $control->getForm();
        }
        throw new BadRequestException('Expected FormControl got ' . \get_class($control));
    }

    /**
     *
     */
    public function render() {

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'QREntryControl.latte');
        $this->template->setTranslator($this->translator);
        $this->template->code = $this->code;
        try {
            $this->template->task = $this->handler->getTaskFromCode($this->code);
            $this->template->team = $this->handler->getTeamFromCode($this->code);
        } catch (TaskCodeException$e) {
        }

        $this->template->render();
    }

    /**
     * @param Form $form
     * @throws ClosedSubmittingException
     */
    private function entryFormSucceeded(Form $form) {
        $values = $form->getValues();
        $httpData = $form->getHttpData();

        $points = 0;
        foreach ($httpData as $key => $value) {
            if (preg_match('/points([0-9])/', $key, $match)) {
                $points = +$match[1];
            }
        }
        try {
            $log = $this->handler->preProcess($values->task_code, $points);
            $this->getPresenter()->flashMessage($log, \BasePresenter::FLASH_SUCCESS);
        } catch (TaskCodeException $e) {
            $this->getPresenter()->flashMessage($e->getMessage(), \BasePresenter::FLASH_ERROR);
        }
    }

    /**
     * @param Form $form
     */
    private function formValidate(Form $form) {
        try {
            $this->handler->checkTaskCode($form->getValues()->task_code);
        } catch (TaskCodeException $e) {
            $form->addError($e->getMessage());
        } catch (ClosedSubmittingException $e) {
            $form->addError($e->getMessage());
        }
    }
}
