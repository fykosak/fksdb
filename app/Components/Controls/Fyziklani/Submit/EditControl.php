<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class EditSubmitControl
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class EditControl extends Control {
    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var ModelFyziklaniSubmit
     */
    private $submit;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * EditSubmitControl constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param ITranslator $translator
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ITranslator $translator) {
        parent::__construct();
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->translator = $translator;
        $this->event = $event;
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
     * @param ModelFyziklaniSubmit $submit
     * @throws BadRequestException
     */
    public function setSubmit(ModelFyziklaniSubmit $submit) {
        $this->submit = $submit;
        $control = $this->getComponent('form');
        if (!$control instanceof FormControl) {
            throw new BadRequestException('Expected FormControl got ' . \get_class($control));
        }
        $control->getForm()->setDefaults([
            'team_id' => $this->submit->e_fyziklani_team_id,
            'points' => $this->submit->points,
        ]);

    }

    /**
     * @return RadioList
     * TODO to table-reflection factory
     */
    private function createPointsField(): RadioList {
        $field = new RadioList(_('Počet bodů'));
        $items = [];
        foreach ($this->event->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
            $items[$points] = $points;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addComponent($this->createPointsField(), 'points');
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->editFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function editFormSucceeded(Form $form) {
        $values = $form->getValues();
        try {
            $msg = $this->submit->changePoints($values->points, $this->getPresenter()->getUser());
            $this->getPresenter()->flashMessage($msg->getMessage(), $msg->getLevel());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'EditControl.latte');
        $this->template->submit = $this->submit;
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }
}
