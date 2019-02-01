<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\model\Fyziklani\TaskCodeException;
use FKSDB\model\Fyziklani\TaskCodeHandler;
use FKSDB\ORM\ModelEvent;
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
        $form->onSuccess[] = function (Form $form) {
            $this->entryFormSucceeded($form);
        };
        return $control;
    }

    public function setCode(string $code) {
        try {
            $this->handler->checkTaskCode($code);
        } catch (TaskCodeException $e) {
            $this->getPresenter()->flashMessage($e->getMessage(), 'danger');
            return;
        }
        /**
         * @var $control FormControl
         */
        $control = $this->getComponent('form');
        $form = $control->getForm();
        $form->setDefaults(['task_code' => $code]);

        foreach ($this->event->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
            /**
             * @var $button Button
             */
            $button = $form['points' . $points];
            $button->setDisabled(false);
        }
    }

    /**
     * @return Form
     */
    public function getForm(): Form {
        /**
         * @var $control FormControl
         */
        $control = $this->getComponent('form');
        return $control->getForm();
    }

    /**
     *
     */
    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'QREntryControl.latte');
        $this->template->render();
    }

    /**
     * @param Form $form
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
            $this->getPresenter()->flashMessage($log, 'success');
        } catch (TaskCodeException $e) {
            $this->getPresenter()->flashMessage($e->getMessage(), 'danger');
        }
    }
}
